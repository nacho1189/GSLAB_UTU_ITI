const togglePassword = document.getElementById('togglePassword');
const inputPass = document.getElementById('password');

togglePassword.addEventListener('click', () => {
  const tipoActual = inputPass.getAttribute('type');
  inputPass.setAttribute('type', tipoActual === 'password' ? 'text' : 'password');

  // Cambia el icono del ojo
    const icono = togglePassword.querySelector('#pass-eye');
    if (tipoActual === 'password') {
        icono.src = '../docs/imgs/icons/eye-svgrepo-com.svg'; // Ojo abierto
    }
    else {
        icono.src = '../docs/imgs/icons/eye-closed-svgrepo-com.svg'; // Ojo cerrado
    }

  
  
});
