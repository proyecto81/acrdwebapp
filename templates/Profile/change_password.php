<?php
$this->assign('title', 'Cambiar Contraseña');
?>

<div class="change-password-container">
    <div class="change-password-header">
        <h2>Cambiar Contraseña</h2>
        <p>Ingrese su contraseña actual y la nueva contraseña</p>
    </div>

    <div class="change-password-form">
        <?= $this->Form->create(null, [
            'class' => 'password-form',
            'id' => 'passwordForm'
        ]) ?>
        
        <div class="form-group">
            <label for="current_password" class="form-label">Contraseña Actual</label>
            <?= $this->Form->control('current_password', [
                'type' => 'password',
                'class' => 'form-control',
                'required' => true,
                'label' => false,
                'id' => 'current_password',
                'placeholder' => 'Ingrese su contraseña actual'
            ]) ?>
        </div>

        <div class="form-group">
            <label for="new_password" class="form-label">Nueva Contraseña</label>
            <?= $this->Form->control('new_password', [
                'type' => 'password',
                'class' => 'form-control',
                'required' => true,
                'label' => false,
                'id' => 'new_password',
                'placeholder' => 'Ingrese su nueva contraseña'
            ]) ?>
            <div class="password-strength">
                <div class="strength-bar">
                    <div class="strength-fill" id="strengthFill"></div>
                </div>
                <div class="strength-text" id="strengthText">Ingrese una contraseña</div>
            </div>
        </div>

        <div class="form-group">
            <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña</label>
            <?= $this->Form->control('confirm_password', [
                'type' => 'password',
                'class' => 'form-control',
                'required' => true,
                'label' => false,
                'id' => 'confirm_password',
                'placeholder' => 'Confirme su nueva contraseña'
            ]) ?>
            <div class="password-match" id="passwordMatch"></div>
        </div>

        <div class="password-requirements">
            <h4>Requisitos de la contraseña:</h4>
            <ul>
                <li id="req-length">Al menos 8 caracteres</li>
                <li id="req-uppercase">Al menos una letra mayúscula</li>
                <li id="req-lowercase">Al menos una letra minúscula</li>
                <li id="req-number">Al menos un número</li>
            </ul>
        </div>

        <div class="form-actions">
            <?= $this->Form->button('CAMBIAR CONTRASEÑA', [
                'type' => 'submit',
                'class' => 'btn btn-primary btn-block',
                'id' => 'submitBtn',
                'disabled' => true
            ]) ?>
            <a href="/profile" class="btn btn-secondary btn-block">
                Cancelar
            </a>
        </div>

        <?= $this->Form->end() ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('passwordForm');
    const currentPasswordInput = document.getElementById('current_password');
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const submitBtn = document.getElementById('submitBtn');
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');
    const passwordMatch = document.getElementById('passwordMatch');

    // Password strength checker
    newPasswordInput.addEventListener('input', function() {
        const password = this.value;
        const strength = checkPasswordStrength(password);
        updatePasswordStrength(strength);
        checkPasswordMatch();
        updateSubmitButton();
    });

    // Password match checker
    confirmPasswordInput.addEventListener('input', function() {
        checkPasswordMatch();
        updateSubmitButton();
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const currentPassword = currentPasswordInput.value.trim();
        const newPassword = newPasswordInput.value.trim();
        const confirmPassword = confirmPasswordInput.value.trim();

        if (!currentPassword || !newPassword || !confirmPassword) {
            alert('Por favor complete todos los campos');
            return;
        }

        if (newPassword !== confirmPassword) {
            alert('Las contraseñas no coinciden');
            return;
        }

        const strength = checkPasswordStrength(newPassword);
        if (strength.score < 3) {
            alert('La contraseña no cumple con los requisitos mínimos');
            return;
        }

        // Show loading state
        submitBtn.textContent = 'Cambiando...';
        submitBtn.disabled = true;

        // Submit form
        form.submit();
    });

    function checkPasswordStrength(password) {
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

    function updatePasswordStrength(strength) {
        const { score, requirements } = strength;
        
        // Update strength bar
        const percentage = (score / 5) * 100;
        strengthFill.style.width = percentage + '%';
        
        // Update strength text and color
        let text = '';
        let color = '';
        
        if (score < 2) {
            text = 'Muy débil';
            color = '#dc3545';
        } else if (score < 3) {
            text = 'Débil';
            color = '#fd7e14';
        } else if (score < 4) {
            text = 'Regular';
            color = '#ffc107';
        } else if (score < 5) {
            text = 'Fuerte';
            color = '#20c997';
        } else {
            text = 'Muy fuerte';
            color = '#28a745';
        }
        
        strengthText.textContent = text;
        strengthFill.style.backgroundColor = color;
        
        // Update requirements
        document.getElementById('req-length').className = requirements.length ? 'met' : '';
        document.getElementById('req-uppercase').className = requirements.uppercase ? 'met' : '';
        document.getElementById('req-lowercase').className = requirements.lowercase ? 'met' : '';
        document.getElementById('req-number').className = requirements.number ? 'met' : '';
    }

    function checkPasswordMatch() {
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (confirmPassword.length === 0) {
            passwordMatch.textContent = '';
            passwordMatch.className = '';
            return;
        }
        
        if (newPassword === confirmPassword) {
            passwordMatch.textContent = '✓ Las contraseñas coinciden';
            passwordMatch.className = 'match';
        } else {
            passwordMatch.textContent = '✗ Las contraseñas no coinciden';
            passwordMatch.className = 'no-match';
        }
    }

    function updateSubmitButton() {
        const currentPassword = currentPasswordInput.value.trim();
        const newPassword = newPasswordInput.value.trim();
        const confirmPassword = confirmPasswordInput.value.trim();
        const strength = checkPasswordStrength(newPassword);
        
        const isValid = currentPassword.length > 0 && 
                       newPassword.length > 0 && 
                       confirmPassword.length > 0 && 
                       newPassword === confirmPassword && 
                       strength.score >= 3;
        
        submitBtn.disabled = !isValid;
    }
});
</script>
