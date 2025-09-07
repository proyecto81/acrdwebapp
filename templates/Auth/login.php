<?php
$this->assign('title', 'Iniciar Sesión');
$this->layout = 'app';
?>

<div class="auth-container">
    <div class="auth-header">
        <div class="auth-logo">
            <div class="logo-icon">🏁</div>
            <h1>Acreditaciones</h1>
            <p>Turismo Nacional</p>
        </div>
    </div>

    <div class="auth-form">
        <?= $this->Form->create(null, [
            'class' => 'login-form',
            'id' => 'loginForm'
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
            <label for="password" class="form-label">Contraseña</label>
            <?= $this->Form->control('password', [
                'type' => 'password',
                'class' => 'form-control',
                'placeholder' => 'Ingrese su contraseña',
                'required' => true,
                'label' => false,
                'id' => 'password'
            ]) ?>
        </div>

        <div class="form-group">
            <?= $this->Form->button('INGRESAR', [
                'type' => 'submit',
                'class' => 'btn btn-primary btn-block'
            ]) ?>
        </div>

        <?= $this->Form->end() ?>

        <div class="auth-links">
            <a href="/recover-password" class="auth-link">¿Olvidaste tu contraseña?</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const dniInput = document.getElementById('dni');
    const passwordInput = document.getElementById('password');

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
        const password = passwordInput.value.trim();

        if (!dni || !password) {
            alert('Por favor complete todos los campos');
            return;
        }

        if (dni.length < 7) {
            alert('El DNI debe tener al menos 7 dígitos');
            return;
        }

        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Ingresando...';
        submitBtn.disabled = true;

        // Submit form
        form.submit();
    });
});
</script>
