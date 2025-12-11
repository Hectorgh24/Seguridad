document.addEventListener('DOMContentLoaded', () => {
  const loginForm = document.querySelector('#loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
      const user = loginForm.nombre.value.trim();
      const pass = loginForm.contrasenia.value.trim();
      if (!user || !pass) {
        alert('Completa usuario y contraseña (validación débil).');
        e.preventDefault();
      }
    });
  }

  const createForm = document.querySelector('#createForm');
  if (createForm) {
    createForm.addEventListener('submit', (e) => {
      if (!createForm.nombre.value || !createForm.contrasenia.value) {
        alert('Completa todos los campos.');
        e.preventDefault();
      }
    });
  }
});

