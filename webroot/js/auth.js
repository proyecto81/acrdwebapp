/**
 * Authentication JavaScript
 * Handles login, logout, and authentication-related functionality
 */

class AuthManager {
    constructor() {
        this.isAuthenticated = false;
        this.currentUser = null;
        this.token = null;
        this.init();
    }

    init() {
        this.loadStoredAuth();
        this.setupEventListeners();
        this.setupFormValidation();
    }

    setupEventListeners() {
        // Login form
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => this.handleLogin(e));
        }

        // Validate email form
        const validateForm = document.getElementById('validateForm');
        if (validateForm) {
            validateForm.addEventListener('submit', (e) => this.handleEmailValidation(e));
        }

        // Recover password form
        const recoverForm = document.getElementById('recoverForm');
        if (recoverForm) {
            recoverForm.addEventListener('submit', (e) => this.handlePasswordRecovery(e));
        }

        // Logout buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="logout"]')) {
                this.handleLogout();
            }
        });

        // Auto-logout on token expiry
        this.setupTokenExpiryCheck();
    }

    setupFormValidation() {
        // DNI input formatting
        const dniInputs = document.querySelectorAll('input[name="dni"]');
        dniInputs.forEach(input => {
            input.addEventListener('input', (e) => this.formatDNI(e));
        });

        // Email validation
        const emailInputs = document.querySelectorAll('input[type="email"]');
        emailInputs.forEach(input => {
            input.addEventListener('blur', (e) => this.validateEmail(e));
        });

        // Password strength checking
        const passwordInputs = document.querySelectorAll('input[type="password"]');
        passwordInputs.forEach(input => {
            input.addEventListener('input', (e) => this.checkPasswordStrength(e));
        });
    }

    formatDNI(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 8) {
            value = value.substring(0, 8);
        }
        e.target.value = value;
    }

    validateEmail(e) {
        const email = e.target.value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const isValid = emailRegex.test(email);

        if (email && !isValid) {
            this.showFieldError(e.target, 'Email inválido');
        } else {
            this.clearFieldError(e.target);
        }

        return isValid;
    }

    checkPasswordStrength(e) {
        const password = e.target.value;
        const strength = this.calculatePasswordStrength(password);
        this.updatePasswordStrengthIndicator(strength);
    }

    calculatePasswordStrength(password) {
        let score = 0;
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /\d/.test(password),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
        };

        Object.values(requirements).forEach(req => {
            if (req) score++;
        });

        return { score, requirements };
    }

    updatePasswordStrengthIndicator(strength) {
        const { score, requirements } = strength;
        
        // Update strength bar
        const strengthFill = document.getElementById('strengthFill');
        if (strengthFill) {
            const percentage = (score / 5) * 100;
            strengthFill.style.width = percentage + '%';
            
            let color = '';
            if (score < 2) color = '#dc3545';
            else if (score < 3) color = '#fd7e14';
            else if (score < 4) color = '#ffc107';
            else if (score < 5) color = '#20c997';
            else color = '#28a745';
            
            strengthFill.style.backgroundColor = color;
        }

        // Update strength text
        const strengthText = document.getElementById('strengthText');
        if (strengthText) {
            const texts = ['Muy débil', 'Débil', 'Regular', 'Fuerte', 'Muy fuerte'];
            strengthText.textContent = texts[score - 1] || 'Ingrese una contraseña';
        }

        // Update requirements
        Object.keys(requirements).forEach(req => {
            const element = document.getElementById(`req-${req}`);
            if (element) {
                element.className = requirements[req] ? 'met' : '';
            }
        });
    }

    async handleLogin(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        const data = {
            dni: formData.get('dni'),
            password: formData.get('password')
        };

        // Validate input
        if (!this.validateLoginData(data)) {
            return;
        }

        try {
            this.showLoadingState(form);
            const response = await this.performLogin(data);
            
            if (response.success) {
                this.setAuthenticated(response.data);
                this.showSuccess('Login exitoso');
                setTimeout(() => {
                    window.location.href = '/';
                }, 1000);
            } else {
                this.showError(response.message || 'Error al iniciar sesión');
            }
        } catch (error) {
            console.error('Login error:', error);
            this.showError('Error de conexión. Intente nuevamente.');
        } finally {
            this.hideLoadingState(form);
        }
    }

    async handleEmailValidation(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        const data = {
            dni: formData.get('dni'),
            email: formData.get('email'),
            password: formData.get('password'),
            confirm_password: formData.get('confirm_password')
        };

        // Validate input
        if (!this.validateEmailValidationData(data)) {
            return;
        }

        try {
            this.showLoadingState(form);
            const response = await this.performEmailValidation(data);
            
            if (response.success) {
                this.setAuthenticated(response.data);
                this.showSuccess('Email validado correctamente');
                setTimeout(() => {
                    window.location.href = '/';
                }, 1000);
            } else {
                this.showError(response.message || 'Error al validar email');
            }
        } catch (error) {
            console.error('Email validation error:', error);
            this.showError('Error de conexión. Intente nuevamente.');
        } finally {
            this.hideLoadingState(form);
        }
    }

    async handlePasswordRecovery(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        const email = formData.get('email');

        if (!this.validateEmail({ target: { value: email } })) {
            this.showError('Por favor ingrese un email válido');
            return;
        }

        try {
            this.showLoadingState(form);
            const response = await this.performPasswordRecovery(email);
            
            if (response.success) {
                this.showSuccess('Se ha enviado un enlace de recuperación a su email');
                setTimeout(() => {
                    window.location.href = '/login';
                }, 2000);
            } else {
                this.showError(response.message || 'Error al enviar email de recuperación');
            }
        } catch (error) {
            console.error('Password recovery error:', error);
            this.showError('Error de conexión. Intente nuevamente.');
        } finally {
            this.hideLoadingState(form);
        }
    }

    validateLoginData(data) {
        if (!data.dni || !data.password) {
            this.showError('Por favor complete todos los campos');
            return false;
        }

        if (data.dni.length < 7) {
            this.showError('El DNI debe tener al menos 7 dígitos');
            return false;
        }

        return true;
    }

    validateEmailValidationData(data) {
        if (!data.dni || !data.email || !data.password || !data.confirm_password) {
            this.showError('Por favor complete todos los campos');
            return false;
        }

        if (data.dni.length < 7) {
            this.showError('El DNI debe tener al menos 7 dígitos');
            return false;
        }

        if (!this.validateEmail({ target: { value: data.email } })) {
            this.showError('Por favor ingrese un email válido');
            return false;
        }

        if (data.password !== data.confirm_password) {
            this.showError('Las contraseñas no coinciden');
            return false;
        }

        if (data.password.length < 6) {
            this.showError('La contraseña debe tener al menos 6 caracteres');
            return false;
        }

        return true;
    }

    async performLogin(data) {
        const response = await fetch('/api/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        return await response.json();
    }

    async performEmailValidation(data) {
        const response = await fetch('/api/auth/validate-email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        return await response.json();
    }

    async performPasswordRecovery(email) {
        const response = await fetch('/api/auth/recover-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email })
        });

        return await response.json();
    }

    setAuthenticated(authData) {
        this.isAuthenticated = true;
        this.currentUser = authData.user;
        this.token = authData.token;
        
        // Store in localStorage
        localStorage.setItem('authToken', this.token);
        localStorage.setItem('currentUser', JSON.stringify(this.currentUser));
        
        // Update UI
        this.updateAuthUI();
    }

    async handleLogout() {
        if (confirm('¿Está seguro que desea cerrar sesión?')) {
            try {
                await this.performLogout();
            } catch (error) {
                console.error('Logout error:', error);
            } finally {
                this.clearAuthentication();
                window.location.href = '/login';
            }
        }
    }

    async performLogout() {
        const response = await fetch('/api/auth/logout', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${this.token}`,
                'Content-Type': 'application/json',
            }
        });

        return await response.json();
    }

    clearAuthentication() {
        this.isAuthenticated = false;
        this.currentUser = null;
        this.token = null;
        
        // Clear localStorage
        localStorage.removeItem('authToken');
        localStorage.removeItem('currentUser');
        
        // Update UI
        this.updateAuthUI();
    }

    loadStoredAuth() {
        const token = localStorage.getItem('authToken');
        const user = localStorage.getItem('currentUser');
        
        if (token && user) {
            try {
                this.token = token;
                this.currentUser = JSON.parse(user);
                this.isAuthenticated = true;
                this.updateAuthUI();
            } catch (error) {
                console.error('Error loading stored auth:', error);
                this.clearAuthentication();
            }
        }
    }

    updateAuthUI() {
        // Update navigation
        const navItems = document.querySelectorAll('.nav-item');
        navItems.forEach(item => {
            if (this.isAuthenticated) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });

        // Update user info in header
        const userName = document.querySelector('.user-name');
        if (userName && this.currentUser) {
            userName.textContent = this.currentUser.name || 'Usuario';
        }
    }

    setupTokenExpiryCheck() {
        // Check token expiry every 5 minutes
        setInterval(() => {
            if (this.token && this.isTokenExpired()) {
                this.handleTokenExpiry();
            }
        }, 300000);
    }

    isTokenExpired() {
        try {
            const payload = JSON.parse(atob(this.token.split('.')[1]));
            return payload.exp * 1000 < Date.now();
        } catch (error) {
            return true;
        }
    }

    async handleTokenExpiry() {
        try {
            await this.refreshToken();
        } catch (error) {
            console.error('Token refresh failed:', error);
            this.clearAuthentication();
            window.location.href = '/login';
        }
    }

    async refreshToken() {
        const response = await fetch('/api/auth/refresh', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${this.token}`,
                'Content-Type': 'application/json',
            }
        });

        if (response.ok) {
            const data = await response.json();
            this.token = data.token;
            localStorage.setItem('authToken', this.token);
        } else {
            throw new Error('Token refresh failed');
        }
    }

    showLoadingState(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.dataset.originalText = submitBtn.textContent;
            submitBtn.textContent = 'Cargando...';
        }
    }

    hideLoadingState(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = submitBtn.dataset.originalText || 'Enviar';
        }
    }

    showError(message) {
        this.showToast(message, 'error');
    }

    showSuccess(message) {
        this.showToast(message, 'success');
    }

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }

    showFieldError(field, message) {
        this.clearFieldError(field);
        
        const errorElement = document.createElement('div');
        errorElement.className = 'field-error';
        errorElement.textContent = message;
        
        field.parentNode.appendChild(errorElement);
        field.classList.add('error');
    }

    clearFieldError(field) {
        const errorElement = field.parentNode.querySelector('.field-error');
        if (errorElement) {
            errorElement.remove();
        }
        field.classList.remove('error');
    }

    getAuthHeaders() {
        return {
            'Authorization': `Bearer ${this.token}`,
            'Content-Type': 'application/json',
        };
    }

    isLoggedIn() {
        return this.isAuthenticated && this.token;
    }

    getCurrentUser() {
        return this.currentUser;
    }
}

// Initialize auth manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.authManager = new AuthManager();
});

// Export for use in other scripts
window.AuthManager = AuthManager;
