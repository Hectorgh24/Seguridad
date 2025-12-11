## Versión Insegura: ataques demostrables

Esta carpeta (`/inseguro`) reproduce vulnerabilidades estilo DVWA (nivel low). Úsala solo en entorno de práctica.

### Login (`index.php`)
- **SQL Injection para bypass de autenticación**: el `SELECT` concatena los valores del formulario. Prueba con usuario `admin' OR '1'='1` y cualquier contraseña; la consulta devuelve filas y abre sesión sin credenciales válidas.
- **Errores visibles**: al no manejar excepciones, un fallo de conexión o de SQL puede filtrar mensajes internos.

### CRUD (`crud.php`)
- **SQL Injection en creación**: `INSERT INTO usuarios ... VALUES ('$nombre', '$password')`. Puedes inyectar valores arbitrarios, por ejemplo cerrar la cadena y añadir otra sentencia si el motor lo permite; también puedes insertar payloads para XSS. Ejemplo de nombre: `Carlos', 'loquesea'); DROP TABLE usuarios; --`.
- **SQL Injection en actualización**: `UPDATE usuarios SET nombre='$nombre', contrasenia='$password' WHERE id=$id`. Control total de `id`, `nombre` y `contrasenia`. Un `id` como `1 OR 1=1` modifica múltiples filas.
- **SQL Injection en borrado**: `DELETE FROM usuarios WHERE id=$id`. Con `?eliminar=0 OR 1=1` se eliminarían todas las filas.
- **XSS reflejado/almacenado en listados**: los valores `nombre` y `contrasenia` se imprimen sin escapar. Inserta `<script>alert('XSS')</script>` en el nombre y verás la ejecución al listar. El placeholder sugiere `Alice<script>alert(1)</script>`.
- **XSS en formularios**: no hay sanitización en la salida de feedback ni en los campos mostrados.

### Redirección (`redirect.php`)
- **Open Redirect**: toma cualquier `?url=` y envía al destino sin validación. Ejemplo: `http://localhost:8080/inseguro/redirect.php?url=https://phishing.ejemplo`. Puede usarse para encadenar ataques de phishing con apariencia legítima del dominio inicial.

### Ejemplos rápidos de payloads
- **Bypass de login (SQLi)**  
  - Usuario: `admin' OR '1'='1`  
  - Contraseña: cualquier texto.
- **Actualizar múltiples filas con SQLi en UPDATE**  
  - ID: `1 OR 1=1`  
  - Nombre: `pwned`  
  - Contraseña: `cambiada`
- **Borrar todo con SQLi en DELETE**  
  - URL: `crud.php?eliminar=0 OR 1=1`
- **XSS almacenado en nombre**  
  - Nombre: `<script>alert('XSS')</script>`  
  - Contraseña: `loquesea`
- **Open redirect**  
  - URL: `redirect.php?url=https://malicioso.ejemplo`
- **Open redirect usado para XSS (data URL)**  
  - URL: `redirect.php?url=data:text/html,<script>alert('XSS via redirect')</script>`  
  - Abre una página controlada por el atacante que ejecuta JavaScript; depende de que el navegador permita `data:` en `Location` (la mayoría lo abre).

### Notas
- No hay controles CSRF, rate limiting, ni validaciones robustas del lado cliente; las validaciones JS son triviales y pueden ser omitidas.
- Las contraseñas se almacenan en texto plano, lo que facilita escalada tras un volcado de base de datos.

