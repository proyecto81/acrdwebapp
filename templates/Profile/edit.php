<?php
$this->assign('title', 'Editar Perfil');
?>

<div class="profile-edit-container">
    <div class="edit-header">
        <h2>Editar Perfil</h2>
        <p>Actualice su información personal</p>
    </div>

    <div class="edit-form">
        <?= $this->Form->create(null, [
            'class' => 'profile-form',
            'id' => 'profileForm'
        ]) ?>
        
        <div class="form-section">
            <h3>Información Personal</h3>
            
            <div class="form-group">
                <label for="name" class="form-label">Nombre Completo</label>
                <?= $this->Form->control('name', [
                    'type' => 'text',
                    'class' => 'form-control',
                    'value' => $user['name'] ?? '',
                    'required' => true,
                    'label' => false,
                    'id' => 'name'
                ]) ?>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <?= $this->Form->control('email', [
                    'type' => 'email',
                    'class' => 'form-control',
                    'value' => $user['email'] ?? '',
                    'required' => true,
                    'label' => false,
                    'id' => 'email'
                ]) ?>
            </div>

            <div class="form-group">
                <label for="phone" class="form-label">Teléfono</label>
                <?= $this->Form->control('phone', [
                    'type' => 'tel',
                    'class' => 'form-control',
                    'value' => $user['phone'] ?? '',
                    'label' => false,
                    'id' => 'phone',
                    'placeholder' => 'Ej: +54 11 1234-5678'
                ]) ?>
            </div>
        </div>

        <div class="form-section">
            <h3>Configuración</h3>
            
            <div class="form-group">
                <label class="form-label">Notificaciones</label>
                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <?= $this->Form->checkbox('notifications', [
                            'checked' => $user['notifications'] ?? true,
                            'id' => 'notifications'
                        ]) ?>
                        <span class="checkbox-text">Recibir notificaciones por email</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <?= $this->Form->button('GUARDAR CAMBIOS', [
                'type' => 'submit',
                'class' => 'btn btn-primary btn-block'
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
    const form = document.getElementById('profileForm');
    const phoneInput = document.getElementById('phone');

    // Format phone input
    phoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 0) {
            if (value.length <= 2) {
                value = `+${value}`;
            } else if (value.length <= 4) {
                value = `+${value.substring(0, 2)} ${value.substring(2)}`;
            } else if (value.length <= 8) {
                value = `+${value.substring(0, 2)} ${value.substring(2, 4)} ${value.substring(4)}`;
            } else {
                value = `+${value.substring(0, 2)} ${value.substring(2, 4)} ${value.substring(4, 8)}-${value.substring(8, 12)}`;
            }
        }
        e.target.value = value;
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();

        if (!name || !email) {
            alert('Por favor complete los campos obligatorios');
            return;
        }

        if (!isValidEmail(email)) {
            alert('Por favor ingrese un email válido');
            return;
        }

        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Guardando...';
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
