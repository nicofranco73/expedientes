// ====================================================================
// SCRIPT: vistas/cambiarPasswordSuperUser_script.js (Ola 4 - Nuevo)
// Contiene el JavaScript específico de la vista cambiarPasswordSuperUser.
// ====================================================================

document.addEventListener('DOMContentLoaded', function() {
    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    const passwordStrength = document.getElementById('passwordStrength');
    const form = document.getElementById('passwordForm');

    /**
     * Evalúa la fuerza de la contraseña.
     * @param {string} password 
     * @returns {{ level: 'weak'|'medium'|'strong', percentage: number }}
     */
    function checkPasswordStrength(password) {
        let score = 0;
        
        // Criterios de fuerza
        if (password.length >= 8) score += 25;
        if (password.match(/[a-z]/)) score += 25;
        if (password.match(/[A-Z]/)) score += 25;
        if (password.match(/[0-9]/)) score += 15;
        if (password.match(/[^a-zA-Z0-9]/)) score += 10;
        
        // Ajuste de niveles (y asegurar un máximo de 100%)
        const percentage = Math.min(score, 100);

        if (percentage < 50) return { level: 'weak', percentage: percentage };
        if (percentage < 80) return { level: 'medium', percentage: percentage };
        return { level: 'strong', percentage: 100 };
    }

    // Validador de fuerza de contraseña
    newPassword.addEventListener('input', function() {
        const password = this.value;
        const strength = checkPasswordStrength(password);
        
        // Actualizar la barra de fuerza
        passwordStrength.className = 'password-strength strength-' + strength.level;
        passwordStrength.style.width = strength.percentage + '%';
        
        // Disparar la verificación de coincidencia al escribir la nueva
        confirmPassword.dispatchEvent(new Event('input')); 
    });

    // Validador de coincidencia de contraseñas
    confirmPassword.addEventListener('input', function() {
        const matchFeedback = document.getElementById('passwordMatch');
        
        if (this.value !== newPassword.value) {
            this.classList.add('is-invalid');
            this.classList.remove('is-valid');
            if(matchFeedback) matchFeedback.style.display = 'block'; 
        } else {
            this.classList.remove('is-invalid');
            if (this.value.length > 0) { // Solo mostrar 'is-valid' si hay contenido
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
            }
            if(matchFeedback) matchFeedback.style.display = 'none'; 
        }
    });

    // Validación final del formulario al enviar
    form.addEventListener('submit', function(e) {
        
        // 1. Verificar coincidencia
        if (newPassword.value !== confirmPassword.value) {
            e.preventDefault();
            alert('Error: Las contraseñas no coinciden. Por favor, verifique.'); 
            confirmPassword.focus();
            confirmPassword.classList.add('is-invalid');
            return false;
        }
        
        // 2. Verificar longitud mínima (mínima de 8 ya está en el HTML, pero se chequea aquí también)
        if (newPassword.value.length < 8) {
            e.preventDefault();
            alert('Error: La nueva contraseña debe tener al menos 8 caracteres.');
            newPassword.focus();
            return false;
        }
        
        // 3. Advertencia de contraseña débil
        const strength = checkPasswordStrength(newPassword.value);
        if (strength.level === 'weak') {
            const confirmed = confirm('Advertencia: La contraseña es débil. ¿Está seguro de que desea continuar?');
            if (!confirmed) {
                e.preventDefault();
                return false;
            }
        }
        
        // Si todo es válido, el formulario se envía
    });
});