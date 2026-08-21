# Documentación de Public

## 1. ¿Qué es `Public/`?

La carpeta `Public` es la raíz pública del proyecto. Aquí viven los archivos accesibles desde el navegador y el punto de entrada HTTP.

Incluye:

- `Public/index.php`
- `Public/css/style.css`
- `Public/js/app.js`

## 2. Flujo de una petición

Cuando el navegador llama a una URL como:

- `/auth`
- `/auth/mostrarRegistro`
- `/dashboard`
- `/mascota`
- `/cita`

la solicitud llega a `Public/index.php` y ese archivo resuelve la ruta usando el nombre del controlador y la acción.

## 3. Routing

El archivo `Public/index.php` hace lo siguiente:

1. limpia la URL
2. separa el controlador y la acción
3. carga la clase correcta
4. ejecuta el método correspondiente
5. devuelve la vista o la redirección

Ejemplo:

```php
$url = trim($_SERVER['REQUEST_URI'], '/');
$partes = explode('/', $url);
$nombreControlador = ucfirst($partes[0]) . 'Controller';
$accion = $partes[1] ?? 'index';
```

Por ejemplo:

- `/auth` -> `AuthController::index()`
- `/auth/login` -> `AuthController::login()`
- `/auth/mostrarRegistro` -> `AuthController::mostrarRegistro()`
- `/auth/mostrarCambioPassword` -> `AuthController::mostrarCambioPassword()`
- `/auth/cambiarPassword` -> `AuthController::cambiarPassword()`
- `/mascota/mostrarCrear` -> `MascotaController::mostrarCrear()`
- `/cita/reagendar/12` -> `CitaController::reagendar(12)`
- `/dashboard` -> `DashboardController::index()`

## 4. Autoload

Se usa `spl_autoload_register()` para cargar clases automáticamente desde:

- `Controllers/`
- `Models/`

## 5. Cómo se sirve el frontend

Las vistas cargan CSS y JS con rutas absolutas:

```php
<link rel="stylesheet" href="/css/style.css">
<script src="/js/app.js" defer></script>
```

Eso significa que el servidor debe levantar la carpeta `Public` como raíz accesible.

## 6. Estado actual del proyecto

El proyecto usa PHP embebido (`php -S`) para desarrollo local:

```bash
php -S 127.0.0.1:8000 -t Public
```

Esto sirve el contenido de `Public` directamente y hace funcionar el enrutamiento sin un framework externo.

## 7. Importante para mantenimiento

- `Public/index.php` no debe duplicar lógica de negocio.
- El routing debe mantenerse simple y claro.
- Al añadir nuevas rutas, también debe añadirse un método en el controlador correspondiente.
- La capa pública es la entrada; la lógica real está en `Controllers` y `Models`.
