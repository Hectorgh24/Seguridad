// Validaciones del lado del cliente como sugiere Víctor
const injectionPattern = /(<|>|script|--|;|\/\*\*\/)/i;

function validarFormulario(form) {
    // Tomo los valores del formulario que me pasen
    const nombreInput = form.querySelector('[name="nombre"]');
    const passInput = form.querySelector('[name="contrasenia"]');
    
    // Verifico el nombre
    if (nombreInput) {
        const value = nombreInput.value.trim();
        
        if (!value) {
            alert('¡Oye! Todos los campos son obligatorios.');
            return false;
        }
        
        // Aquí bloqueo caracteres peligrosos típicos de XSS y SQLi
        if (injectionPattern.test(value)) {
            alert('⚠️ JS Bloqueó el envío: Se detectaron caracteres peligrosos (Intento de inyección).');
            return false;
        }
        
        if (value.length > 50) {
            alert('El nombre es demasiado largo.');
            return false;
        }
    }
    
    // Verifico la contraseña
    if (passInput) {
        const value = passInput.value.trim();
        if (value.length < 1) { // Víctor sugiere 6, pero para pruebas rápidas puse 1
             alert('La contraseña no puede estar vacía.');
             return false;
        }
    }

    // Si todo está bien, dejo pasar el formulario
    return true;
}
