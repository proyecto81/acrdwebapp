<?php
$this->assign('title', 'Validar Email');
$this->layout = 'app';
?>

<div class="auth-container">
    <div class="auth-header">
        <div class="auth-logo">
            <div class="logo-icon">📧</div>
            <h1>Validar Email</h1>
            <p>Complete su perfil</p>
        </div>
    </div>

    <div class="auth-form">
        <?= $this->Form->create(null, [
            'class' => 'validate-form',
            'id' => 'validateForm'
        ]) ?>
        
        <div class="form-group">
            <label for="dni" class="form-label">DNI</label>
            <?= $this->Form->control('dni', [
                'type' => 'text',
                'class' => 'form-control',
                'placeholder' => 'Ingrese su DNI',
                'required' => true,
                'label' => false,
                'id' => 'dni'
            ]) ?>
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <?= $this->Form->control('email', [
                'type' => 'email',
                'class' => 'form-control',
                'placeholder' => 'Ingrese su email',
                'required' => true,
                'label' => false,
                'id' => 'email'
            ]) ?>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Contraseña</label>
            <?= $this->Form->control('password', [
                'type' => 'password',
                'class' => 'form-control',
                'placeholder' => 'Cree una contraseña',
                'required' => true,
                'label' => false,
                'id' => 'password'
            ]) ?>
        </div>

        <div class="form-group">
            <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
            <?= $this->Form->control('confirm_password', [
                'type' => 'password',
                'class' => 'form-control',
                'placeholder' => 'Confirme su contraseña',
                'required' => true,
                'label' => false,
                'id' => 'confirm_password'
            ]) ?>
        </div>

        <div class="form-group">
            <?= $this->Form->button('VALIDAR EMAIL', [
                'type' => 'submit',
                'class' => 'btn btn-primary btn-block'
            ]) ?>
        </div>

        <?= $this->Form->end() ?>

        <div class="auth-links">
            <a href="/login" class="auth-link">Volver al login</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('validateForm');
    const dniInput = document.getElementById('dni');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');

    // Format DNI input
    dniInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 8) {
            value = value.substring(0, 8);
        }
        e.target.value = value;
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const dni = dniInput.value.trim();
        const email = emailInput.value.trim();
        const password = passwordInput.value.trim();
        const confirmPassword = confirmPasswordInput.value.trim();

        if (!dni || !email || !password || !confirmPassword) {
            alert('Por favor complete todos los campos');
            return;
        }

        if (dni.length < 7) {
            alert('El DNI debe tener al menos 7 dígitos');
            return;
        }

        if (!isValidEmail(email)) {
            alert('Por favor ingrese un email válido');
            return;
        }

        if (password !== confirmPassword) {
            alert('Las contraseñas no coinciden');
            return;
        }

        if (password.length < 6) {
            alert('La contraseña debe tener al menos 6 caracteres');
            return;
        }

        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Validando...';
        submitBtn.disabled = true;

        // Submit form
        form.submit();
    });

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
});
</script>
