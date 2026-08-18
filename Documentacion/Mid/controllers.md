# Documentación de Controllers

## 1. ¿Qué hace una capa de controladores?

Los controladores son la capa intermedia entre:

- la URL enviada por el navegador,
- el modelo que accede a la base de datos,
- la vista que renderiza la respuesta HTML.

En este proyecto, cada entidad tiene su propio controlador:

- `AuthController`
- `MascotaController`
- `CitaController`
- `DashboardController`
- `UsuarioController`
- `HistorialController`
- `VacunaController`

## 2. Clase base `Controller`

Archivo: `Controllers/Controller.php`

Esta clase abstracta centraliza métodos reutilizables para todos los controladores. Los principales son:

### `vista(string $ruta, array $datos = [])`

```php
protected function vista(string $ruta, array $datos = []): void
```

Esta función:

- prepara variables por defecto,
- hace `extract($datos)`,
- busca la vista en `Views/...`,
- incluye el archivo PHP de la vista.

Ejemplo:

```php
$this->vista('dashboard/index', ['nombre' => 'Ana']);
```

Eso significa que en la vista se puede usar directamente `$nombre` sin pasar por un objeto complicado.

### `redireccionar(string $url)`

```php
header("Location: $url");
exit;
```

Sirve para redirigir al usuario a otra ruta, por ejemplo:

- `/dashboard`
- `/auth`
- `/cita`

### `json(array $datos, int $codigo = 200)`

Genera una respuesta JSON con el código HTTP indicado.

### `requiereSesion(array $rolesPermitidos = [])`

Este método valida sesión:

```php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    $this->redireccionar('/auth');
}
```

Y si se le pasa un conjunto de roles, también comprueba si el usuario tiene permiso.

## 3. `AuthController`

Archivo: `Controllers/AuthController.php`

### Responsabilidad

Toda la autenticación del sistema:

- mostrar login,
- registrar usuarios,
- iniciar sesión,
- cambiar contraseña,
- cerrar sesión.

### `index()`

Muestra la vista de login.

### `mostrarRegistro()`

Muestra el formulario de registro.

### `registrar()`

Valida:

- campos obligatorios,
- fortaleza de contraseña,
- coincidencia entre contraseña y confirmación,
- correo duplicado,
- rol por defecto para dueño de mascota.

Si todo es correcto, crea al usuario con `rol_id = 4` (DueñoMascota).

### `login()`

- recoge correo y contraseña,
- busca el usuario por email,
- valida hash de contraseña,
- verifica si usuario está activo,
- revisa bloqueo temporal por muchos intentos fallidos,
- guarda datos en sesión.

Ejemplo de sesión:

```php
$_SESSION['usuario_id'] = $usuario['id'];
$_SESSION['nombre'] = $usuario['nombre'];
$_SESSION['rol_id'] = $usuario['rol_id'];
```

### `mostrarCambioPassword()`

Muestra formulario para cambiar contraseña temporal.

### `cambiarPassword()`

Verifica la nueva contraseña, la confirma y actualiza en base de datos.

### `logout()`

Destruye la sesión y redirige al login.

## 4. `MascotaController`

Archivo: `Controllers/MascotaController.php`

### Responsabilidad

Gestión de mascotas y su ciclo de vida.

### `index()`

- exige sesión,
- si el rol es `DueñoMascota`, carga solo sus mascotas,
- si es staff, carga todas las activas,
- muestra mensaje flash desde sesión.

### `mostrarCrear()`

Muestra formulario para registrar una mascota.

### `crear()`

Valida datos obligatorios y guarda la mascota vinculada al usuario autenticado.

### `mostrarEditar(int $id)`

Busca la mascota por ID y la envía a la vista de edición.

### `editar(int $id)`

Valida los datos y actualiza el registro.

### `eliminar(int $id)`

En lugar de borrar físicamente la mascota, la da de baja con lógica (`activa = 0`).

### `buscar()`

Busca mascotas por texto y reusa la vista de listado.

## 5. `CitaController`

Archivo: `Controllers/CitaController.php`

### Responsabilidad

Administrar citas veterinarias.

### `index()`

Dependiendo del rol:

- Dueño: muestra sus citas,
- Veterinario: muestra citas asignadas,
- default: muestra todas.

### `mostrarCrear()`

Crea la vista de agendamiento con:

- lista de mascotas del usuario o del sistema,
- lista de veterinarios disponibles.

### `crear()`

Valida que todos los campos existan y que el horario esté disponible.

Si el horario está ocupado, genera sugerencias de horarios libres.

### `recargarFormularioConError()`

Método privado para volver a mostrar el formulario con errores y sugerencias.

### `cancelar(int $id)`

Antes de cancelar, comprueba si faltan más de 24 horas para la cita. Si no, se rechaza la cancelación.

### `mostrarReagendar(int $id)`

Carga la edición para reagendar una cita.

### `reagendar(int $id)`

Valida fecha/hora nueva y verifica disponibilidad. Si está libre, crea una nueva cita y marca la original como `reagendada`.

## 6. `DashboardController`

Archivo: `Controllers/DashboardController.php`

### Responsabilidad

Mostrar el panel principal según el rol del usuario:

- Dueño: mascotas y próximas citas,
- Veterinario: agenda asignada,
- Administrador/Recepcionista: resumen general y cantidad de usuarios/mascotas.

### `index()`

- valida sesión,
- identifica rol del usuario,
- llama a los modelos necesarios,
- envía datos a la vista `dashboard/index`.

## 7. `UsuarioController`

Archivo: `Controllers/UsuarioController.php`

Responsabilidad: gestión de cuentas de usuarios por parte del administrador, incluyendo:

- listar usuarios,
- crear usuarios por admin,
- editar información,
- activar/desactivar cuentas,
- restablecer contraseñas,
- consultar roles.

## 8. `HistorialController`

Archivo: `Controllers/HistorialController.php`

Responsabilidad: manejar el historial clínico de cada mascota, incluyendo:

- registrar entrada clínica,
- visualizar historial,
- editar información dentro del plazo permitido,
- relacionarlo con citas y tratamientos.

## 9. `VacunaController`

Archivo: `Controllers/VacunaController.php`

Responsabilidad: registrar vacunas y desparasitaciones, además de consultar alertas y filtros por mascota.

## 10. Cómo se conectan todos los controladores

Los controladores siguen este esquema general:

1. validar sesión,
2. leer datos de `$_POST` o `$_GET`,
3. llamar a un modelo,
4. comprobar resultado,
5. mostrar vista o redirigir.

Es decir, los controladores actúan como la lógica de aplicación, no como acceso directamente a la base de datos.

## 11. Resumen

La capa de controladores tiene dos tareas esenciales:

- decidir qué debe ocurrir según la URL y el usuario,
- coordinar entre la vista y los modelos.

Sin esta capa, la aplicación no sabría qué hacer cuando un usuario realiza una acción como iniciar sesión, registrar una mascota o agendar una cita.
