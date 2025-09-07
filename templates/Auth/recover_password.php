<?php
$this->assign('title', 'Recuperar Contraseña');
$this->layout = 'app';
?>

<div class="auth-container">
    <div class="auth-header">
        <div class="auth-logo">
            <div class="logo-icon">🔒</div>
            <h1>Recuperar Contraseña</h1>
            <p>Ingrese su email para recibir un enlace de recuperación</p>
        </div>
    </div>

    <div class="auth-form">
        <?= $this->Form->create(null, [
            'class' => 'recover-form',
            'id' => 'recoverForm'
        ]) ?>
        
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
            <?= $this->Form->button('ENVIAR ENLACE', [
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
    const form = document.getElementById('recoverForm');
    const emailInput = document.getElementById('email');

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = emailInput.value.trim();

        if (!email) {
            alert('Por favor ingrese su email');
            return;
        }

        if (!isValidEmail(email)) {
            alert('Por favor ingrese un email válido');
            return;
        }

        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Enviando...';
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
