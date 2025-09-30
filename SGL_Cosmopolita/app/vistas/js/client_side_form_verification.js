// CI formulario
const user_form_verifcation_enter = () => {
    document.addEventListener('DOMContentLoaded', () => {
        const inputCI = document.getElementById('ci');
        const mensaje = document.getElementById('mensaje');

    inputCI.addEventListener('input', () => {
        const valor = inputCI.value;

        //Vuelve visible el mensaje de error
        mensaje.style.display = 'block';

        // Elimina cualquier carácter no numérico
        const soloNumeros = valor.replace(/\D/g, '');

        // Actualiza el valor del input con solo números y máximo 8 dígitos
        inputCI.value = soloNumeros.slice(0, 8);

        // Validaciones y mensajes
        if (valor !== soloNumeros) {
        mensaje.textContent = "Solo se permiten números (sin puntos ni guiones).";
        } else if (soloNumeros.length < 8) {
        mensaje.textContent = "Ingrese 8 dígitos numéricos.";
        } else {
        mensaje.textContent = ""; // Sin errores
        mensaje.style.display = 'none'; // Oculta el mensaje si no hay errores
        }
    });
});
}
user_form_verifcation_enter()

// Contraseña formulario
const user_form_verifcation_password = () => {
    document.addEventListener('DOMContentLoaded', () => {
    // Referencias a los elementos
    const inputPass = document.getElementById('password');
    const mensajePass = document.getElementById('mensajePassword');

    inputPass.addEventListener('input', () => {
        let valor = inputPass.value;

        // Vuelve visible el mensaje de error
        mensajePass.style.display = 'block';

        // Limita caracteres a letras y números solamente
        const soloValidos = valor.replace(/[^a-zA-Z0-9]/g, '');

        // Recorta a 10 caracteres válidos
        inputPass.value = soloValidos.slice(0, 16);

    // Validaciones
        const tieneMinuscula = /[a-z]/.test(soloValidos);
        const tieneMayuscula = /[A-Z]/.test(soloValidos);
        const tieneNumero = /[0-9]/.test(soloValidos);

        if (valor !== soloValidos) {
            mensajePass.textContent = "Solo se permiten letras y números (sin símbolos ni espacios).";
        } /*else if (soloValidos.length < 10) {
            mensajePass.textContent = "La contraseña debe tener exactamente 16 caracteres.";
        } else if (!tieneMinuscula || !tieneMayuscula || !tieneNumero) {
            mensajePass.textContent = "Debe contener al menos una minúscula, una mayúscula y un número.";
        }*/ else {
            // Todo correcto
            mensajePass.textContent = ""; 
            mensajePass.style.display = "none"
            

            }
        });
    });
}
user_form_verifcation_password()