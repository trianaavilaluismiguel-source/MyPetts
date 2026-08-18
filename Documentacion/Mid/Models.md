# Documentación de Models

## 1. ¿Qué es un modelo?

En este proyecto, cada clase en `Models/` representa una entidad o conjunto de operaciones sobre una tabla de la base de datos.

El patrón es muy simple:

- `Model` = clase base con CRUD general.
- `Usuario`, `Mascota`, `Cita`, `HistorialClinico`, `VacunaDesparasitacion` = clases específicas por entidad.

## 2. Clase base `Model`

Archivo: `Models/Model.php`

```php
abstract class Model
{
    protected PDO $db;
    protected string $tabla;
}
```

### ¿Qué hace?

- tiene la conexión a la base de datos,
- define la tabla sobre la que se trabaja,
- ofrece métodos reutilizables para consultar, crear, actualizar y borrar registros.

### Constructor

```php
public function __construct()
{
    $conexion = new Conexion();
    $this->db = $conexion->conectar();
}
```

Esto quiere decir que cada modelo obtiene una conexión PDO al instanciarse. Así no hay que escribir la conexión una y otra vez.

### Método `todos()`

```php
public function todos(): array
{
    $stmt = $this->db->query("SELECT * FROM {$this->tabla}");
    return $stmt->fetchAll();
}
```

Devuelve todos los registros de la tabla.

### Método `buscarPorId(int $id)`

```php
public function buscarPorId(int $id): array|false
```

Busca un registro por la clave primaria `id`.

### Método `crear(array $datos)`

```php
public function crear(array $datos): string
```

- toma un array asociativo,
- arma columnas y placeholders,
- insertará la fila,
- devuelve el ID autogenerado.

### Método `actualizar(int $id, array $datos)`

Actualiza columnas según el array recibido.

### Método `eliminar(int $id)`

Elimina un registro por `id`.

## 3. Modelo `Usuario`

Archivo: `Models/Usuario.php`

### Propiedad principal

```php
protected string $tabla = 'usuarios';
```

### Métodos principales

#### `buscarPorCorreo(string $correo)`

Busca un usuario por email.

Se usa para:

- validar si un correo ya existe en registro,
- verificar credenciales de login,
- identificar al usuario actual por sesión.

#### `registrar(...)`

```php
$hash = password_hash($contrasenaPlana, PASSWORD_DEFAULT);
```

Esto crea una contraseña segura con hash. Nunca se guarda la contraseña en texto plano.

#### `verificarContrasena(string $contrasenaPlana, string $hashGuardado)`

Usa `password_verify()` para comparar la contraseña introducida con el hash almacenado.

#### `buscarPorRol(int $rolId)`

Devuelve todos los usuarios activos de un rol concreto, por ejemplo veterinarios.

#### `listarConRol()`

Hace un `JOIN` con la tabla `roles` para devolver además el nombre del rol asociado.

#### `obtenerRoles()`

Devuelve el catálogo de roles para llenar un selector del formulario.

#### `crearPorAdmin(...)`

Crea una cuenta desde un administrador asignando:

- nombre,
- correo,
- teléfono,
- rol,
- creador,
- contraseña temporal,
- `requiere_cambio_pwd = 1`.

Esto obliga al usuario a cambiar la contraseña al primer inicio.

#### `actualizarDatos(...)`

Actualiza la información básica del usuario sin tocar la contraseña.

#### `cambiarEstado(...)`

Activa o desactiva la cuenta.

#### `resetearContrasena(...)`

Genera una contraseña temporal y la reemplaza por hash.

#### `cambiarContrasena(...)`

Cambia la clave actual por una nueva y limpia la obligación de cambiarla en el próximo login.

## 4. Modelo `Mascota`

Archivo: `Models/Mascota.php`

### Propiedad

```php
protected string $tabla = 'mascotas';
```

### Métodos

#### `buscarPorDueno(int $duenoId)`

Muestra las mascotas activas de un dueño concreto.

#### `todosActivos()`

Muestra todas las mascotas activas del sistema.

#### `darDeBaja(int $id)`

No elimina el registro, solo hace `activa = 0`.

Esto preserva el historial de la mascota.

#### `reactivar(int $id)`

Marca la mascota como activa nuevamente.

#### `buscar(string $termino)`

Busca por nombre, especie o número de registro (`id`).

#### `etiquetaEstadoSalud(string $valor)`

Traduce valores internos del enum a texto legible:

- `al_dia` -> `Al día`
- `pendiente_atencion` -> `Pendiente de atención`

#### `calcularEdad(string $fechaNacimiento)`

Calcula la edad usando `DateTime` y devuelve un valor como:

- `2 años`
- `5 meses`

## 5. Modelo `Cita`

Archivo: `Models/Cita.php`

### Propósito

Esta clase representa toda la lógica de citas veterinarias.

### Métodos principales

#### `buscarPorDueno(int $duenoId)`

Trae las citas del dueño, con el nombre de la mascota y del veterinario.

#### `buscarPorVeterinario(int $veterinarioId)`

Trae todas las citas asignadas a un veterinario concreto.

#### `todas()`

Lista todas las citas con joins para mostrar mascotas y veterinarios.

#### `horarioDisponible(int $veterinarioId, string $fecha, string $hora)`

Verifica si ese horario ya está ocupado por otra cita del mismo veterinario.

#### `sugerirHorariosLibres(...)`

Busca 3 horarios disponibles a partir de una fecha pedida, incluso recorriendo días próximos.

#### `cancelar(int $id, string $motivo)`

Actualiza el estado a `cancelada` y registra la razón.

#### `proximasPorDueno(...)`

Devuelve las próximas citas del dueño y las limita por un número máximo.

#### `proximasPorVeterinario(...)`

Lo mismo para veterinario.

#### `proximasTodas(...)`

Agenda general para recepcionista o administrador.

#### `reagendar(array $citaOriginal, string $nuevaFecha, string $nuevaHora)`

No reemplaza la cita original. Crea una nueva fila y actualiza la original a `reagendada`.

Eso permite conservar trazabilidad y historial de cambios.

## 6. Modelo `HistorialClinico`

Archivo: `Models/HistorialClinico.php`

### Propósito

Registrar la historia clínica de una mascota.

### Métodos

#### `registrarEntrada(array $datos)`

Crea una entrada con:

- mascota
- cita asociada
- veterinario
- motivo
- diagnóstico
- tratamiento
- observaciones

#### `buscarPorMascota(int $mascotaId)`

Devuelve el historial de una mascota ordenado por fecha descendente.

#### `puedeEditar(array $entrada)`

Comprueba si aún no pasaron más de 24 horas desde que el registro fue hecho.

#### `editarEntrada(int $id, array $datos, int $editorId)`

Actualiza la entrada y guarda quién la editó y a qué hora.

## 7. Modelo `VacunaDesparasitacion`

Archivo: `Models/VacunaDesparasitacion.php`

### Propósito

Guardar vacunas y desparasitaciones aplicadas.

### Métodos

#### `registrar(array $datos)`

Inserta una dosis de vacuna o desparasitación.

#### `buscarPorMascota(int $mascotaId)`

Devuelve todas las aplicaciones con un campo `estado_alerta`, calculado asumiendo:

- `vencida` si la fecha de próxima dosis ya pasó,
- `proxima` si falta menos de 30 días,
- `vigente` en caso contrario.

#### `buscarAlertas()`

Lista las vacunas/desparasitaciones que están próximas a vencer o vencidas.

#### `etiquetaTipo(string $valor)`

Convierte:

- `vacuna` -> `Vacuna`
- `desparasitacion` -> `Desparasitación`

#### `colorAlerta(string $estado)`

Devuelve un color para la vista según el estado de alerta.

## 8. Relación entre models y controladores

Cada controlador toma decisiones y cada modelo hace las consultas reales.

Ejemplo:

- `MascotaController` llama a `Mascota::buscarPorDueno()`
- `CitaController` llama a `Cita::horarioDisponible()`
- `AuthController` usa `Usuario::buscarPorCorreo()`
- `HistorialController` usa `HistorialClinico::registrarEntrada()`

Esto mantiene el código organizado y evita mezclar SQL dentro de los controladores.

## 9. Ventaja del enfoque actual

El uso de modelos centraliza el acceso a la base de datos. Eso hace que:

- el código sea más limpio,
- las consultas queden reutilizables,
- sea más sencillo hacer cambios de estructura,
- se reduzcan errores de duplicación.

## 10. Resumen

Los modelos son la capa de persistencia del proyecto. Son responsables de:

- consultar datos,
- insertar registros,
- actualizar información,
- encapsular SQL y validaciones de negocio relacionadas con la base de datos.

Sin esta capa, los controladores tendrían que manejar SQL directamente, lo que haría el proyecto poco mantenible.
