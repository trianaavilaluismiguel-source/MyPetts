# Documentación de Base de Datos

## 1. ¿Qué hace esta capa?

La base de datos almacena la información del sistema de veterinaria: usuarios, mascotas, citas, historial clínico y vacunas. El proyecto usa MySQL con PDO y una clase `Conexion` que centraliza la conexión.

Las piezas principales son:

- `Database/Conexion.php` – conexión a la base de datos.
- `Sql/mypetts.sql` – script completo de creación y carga inicial.

## 2. Conexión a la base de datos

El archivo `Database/Conexion.php` define una clase llamada `Conexion`:

```php
class Conexion
{
    private $host = 'localhost';
    private $dbname = 'mypetts';
    private $port = '3306';
    private $usuario = 'admin';
    private $clave = 'root';
}
```

Luego se construye el DSN:

```php
$dsn = "mysql:host=$this->host;dbname=$this->dbname;charset=utf8mb4";
```

Y se crea la conexión con PDO:

```php
$pdo = new PDO($dsn, $this->usuario, $this->clave);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
```

### ¿Por qué es importante?

- `PDO` es una capa de acceso a bases de datos.
- `ERRMODE_EXCEPTION` hace que cualquier error de SQL lance excepción, facilitando depuración.
- La conexión queda encapsulada, para que cada modelo no tenga que volver a conectarse manualmente.

## 3. ¿Cómo se usa?

Cada modelo de la carpeta `Models` hace algo como esto:

```php
require_once __DIR__ . '/../Database/Conexion.php';
```

y luego en el constructor:

```php
$conexion = new Conexion();
$this->db = $conexion->conectar();
```

Eso significa que todos los modelos comparten la misma conexión lógica centralizada.

## 4. Estructura general de la base de datos

El script `Sql/mypetts.sql` crea la base de datos `mypetts` y todas las tablas necesarias.

### 4.1 Tabla `roles`

Guarda los tipos de usuario del sistema:

- Administrador
- Veterinario
- Recepcionista
- DueñoMascota

Es la base para la lógica de permisos y roles.

### 4.2 Tabla `usuarios`

Representa los usuarios del sistema.

Campos clave:

- `id`
- `nombre`
- `correo`
- `contrasena_hash`
- `telefono`
- `rol_id`
- `activo`
- `intentos_fallidos`
- `bloqueado_hasta`
- `requiere_cambio_pwd`
- `creado_por`

Relación principal:

- muchos usuarios pertenecen a un rol (`usuarios.rol_id` -> `roles.id`)

Esto permite distinguir quién puede hacer qué.

### 4.3 Tabla `tokens_recuperacion`

Permite manejar recuperación de contraseña por token.

Campos clave:

- `usuario_id`
- `token`
- `expira_en`
- `usado`

Tiene relación directa con `usuarios` y está pensada para la recuperación de credenciales.

### 4.4 Tabla `mascotas`

Representa las mascotas registradas.

Campos importantes:

- `dueno_id` – propietario
- `nombre`
- `especie`
- `raza`
- `fecha_nacimiento`
- `sexo`
- `peso`
- `estado_salud`
- `activa`

Relación:

- cada mascota pertenece a un dueño (`dueno_id` -> `usuarios.id`)

Además, la baja no se elimina físicamente, sino que se marca con `activa = 0`.

### 4.5 Tabla `citas`

Almacena las citas veterinarias.

Campos importantes:

- `mascota_id`
- `veterinario_id`
- `agendada_por`
- `fecha`
- `hora`
- `tipo_consulta`
- `estado`
- `cita_origen_id`
- `motivo_cancelacion`

Relaciones:

- cada cita pertenece a una mascota,
- cada cita está asignada a un veterinario,
- la cita puede ser creada por un dueño o un recepcionista,
- el campo `cita_origen_id` permite llevar historial de reagendamiento.

La restricción `UNIQUE KEY uq_cita_horario (veterinario_id, fecha, hora)` evita duplicar horarios.

### 4.6 Tabla `historial_clinico`

Guarda la historia médica de cada mascota.

Campos relevantes:

- `mascota_id`
- `cita_id`
- `veterinario_id`
- `motivo_consulta`
- `diagnostico`
- `tratamiento`
- `observaciones`
- `fecha_registro`
- `fecha_ultima_edicion`
- `editado_por`

Esto permite una trazabilidad completa de cada atención.

### 4.7 Tabla `vacunas_desparasitaciones`

Guarda administraciones de vacunas o desparasitaciones.

Campos clave:

- `mascota_id`
- `historial_id`
- `veterinario_id`
- `tipo`
- `nombre_producto`
- `lote`
- `fecha_aplicacion`
- `fecha_proxima_dosis`
- `aplicada_siguiente`

Se usa para calcular alertas de próximo vencimiento.

### 4.8 Tabla `notificaciones`

Almacena mensajes y alertas enviadas al usuario.

Se usa para:

- recordatorios de cita,
- recordatorios de vacuna,
- alertas de vacunación vencida,
- otros avisos del sistema.

### 4.9 Tabla `reportes_generados`

Registra reportes creados por administradores o veterinarios.

Campos:

- `usuario_id`
- `tipo_reporte`
- `fecha_inicio`
- `fecha_fin`
- `formato_exportacion`

## 5. Relaciones entre tablas

La base de datos tiene un diseño relacional con estas conexiones principales:

- `usuarios` -> `roles`
- `mascotas` -> `usuarios`
- `citas` -> `mascotas`
- `citas` -> `usuarios` (veterinario)
- `historial_clinico` -> `mascotas`
- `historial_clinico` -> `usuarios`
- `vacunas_desparasitaciones` -> `mascotas`
- `vacunas_desparasitaciones` -> `historial_clinico`
- `notificaciones` -> `usuarios`
- `reportes_generados` -> `usuarios`

## 6. Uso de índices y constraints

El script incluye claves foráneas, índices y constraints para mantener integridad:

- `FOREIGN KEY` para prevenir datos huérfanos.
- `UNIQUE` para no repetir correo o horarios duplicados.
- `INDEX` para mejorar búsquedas comunes como por rol, fecha o estado.

Este diseño evita errores de consistencia y acelera consultas frecuentes.

## 7. Vista `vw_estadisticas_generales`

El script crea una vista llamada:

```sql
CREATE OR REPLACE VIEW vw_estadisticas_generales AS
SELECT ...
```

Esto sirve para consultar estadísticas generales sin crear tablas adicionales:

- total de usuarios activos,
- mascotas registradas,
- citas completadas del mes,
- citas canceladas del mes,
- vacunas aplicadas del mes.

## 8. ¿Cómo se conecta el proyecto con esta base?

La conexión no está en cada controlador, sino en la clase base `Model`:

```php
$conexion = new Conexion();
$this->db = $conexion->conectar();
```

Cada modelo hereda de `Model`, así que el acceso a la DB queda unificado y reutilizable.

## 9. Importante sobre la seguridad y consistencia

El proyecto usa:

- contraseñas con `password_hash()`
- validaciones en PHP,
- `PDO` con excepciones,
- integridad referencial,
- diseño relacional con claves foráneas.

Esto da un nivel razonable de seguridad para una aplicación de gestión clínica pequeña.

## 10. Resumen

La capa de base de datos es la columna vertebral del sistema. Guarda:

- quiénes son los usuarios y qué rol tienen,
- qué mascotas existen,
- cuántas citas hay,
- qué pasó en el historial clínico,
- qué vacunas y desparasitaciones se aplicaron,
- qué alertas se generaron.

Sin esta base, el resto del patrón MVC no podría funcionar.
