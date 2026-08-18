# Documentación de Controllers

## 1. Propósito

Los controladores son la capa intermedia entre la URL, el modelo y la vista. En este proyecto cada entidad principal tiene un controlador de la forma `NombreController` dentro de `Controllers/`.

Los controladores más relevantes son:

- `AuthController`
- `DashboardController`
- `MascotaController`
- `CitaController`
- `UsuarioController`
- `HistorialController`
- `VacunaController`

## 2. Base `Controller`

El archivo `Controllers/Controller.php` centraliza la lógica reutilizable:

- `vista(string $ruta, array $datos = [])`
- `redireccionar(string $url)`
- `json(array $datos, int $codigo = 200)`
- `requiereSesion(array $rolesPermitidos = [])`

### `vista()`

La vista se incluye directamente con PHP, sin un layout global. Esto elimina la doble capa de render y evita que una vista se cargue dentro de un layout antiguo que ya no existe.

```php
$this->vista('auth/login', ['error' => 'Acceso denegado.']);
```

### `requiereSesion()`

Valida que exista una sesión activa. Si no, redirige a `/auth`.

## 3. `AuthController`

Coordinación de login, registro, cambio de contraseña y cierre de sesión.

### Funciones clave

- `index()` -> muestra login
- `mostrarRegistro()` -> muestra registro
- `registro()` -> alterna entre mostrar el formulario y procesar el POST
- `registrar()` -> valida campos, contraseña y correo duplicado
- `login()` -> autentica con hash y crea la sesión
- `olvidePassword()` -> muestra la vista de recuperación / cambio de contraseña
- `mostrarCambioPassword()` -> formulario de cambio obligatorio
- `cambiarPassword()` -> actualiza contraseña y quita la obligación
- `logout()` -> destruye sesión

La autenticación sigue un flujo claro:

1. usuario entra al login,
2. si necesita registro, pasa a la pantalla de alta,
3. luego inicia sesión y, si aplica, cambia la contraseña temporal,
4. finalmente accede al dashboard.

## 4. `DashboardController`

Prepara el contenido principal del panel según el rol del usuario.

- Dueño: mascotas y próximas citas
- Veterinario: su agenda
- Staff: resumen general y agenda del sistema

## 5. `MascotaController`

Gestiona el ciclo completo de mascotas:

- listar
- buscar
- crear
- editar
- dar de baja
- reactivar si aplica

## 6. `CitaController`

Gestiona citas veterinarias:

- listar por rol
- crear
- validar horarios
- sugerir horarios alternativos si está ocupado
- cancelar con regla de 24 horas
- reagendar conservando historial

## 7. Buenas prácticas del proyecto

- La lógica de negocio va en el controlador.
- Las consultas van en el modelo.
- Las vistas solo renderizan HTML y mensajes.
- Las redirecciones se hacen con `Location` y `exit`.
- La seguridad de sesiones debe comprobarse en cada acción sensible.

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
