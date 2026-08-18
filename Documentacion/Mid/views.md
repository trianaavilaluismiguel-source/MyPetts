# Documentación de Views

## 1. ¿Qué son las vistas?

Las vistas son el código PHP que genera el HTML visible para el usuario. Se encuentran en la carpeta `Views/` y representan cada pantalla del sistema.

Ejemplos:

- `Views/auth/login.php`
- `Views/dashboard/index.php`
- `Views/mascotas/crear.php`
- `Views/citas/crear.php`
- `Views/partials/header.php`
- `Views/partials/footer.php`

## 2. Estructura principal

La carpeta `Views` está organizada por módulos:

- `auth/` – login y registro
- `dashboard/` – panel principal
- `mascotas/` – gestión de mascotas
- `citas/` – agendar y consultar citas
- `usuarios/` – usuarios del sistema
- `historial/` – historial clínico
- `vacunas/` – vacunas y desparasitaciones
- `partials/` – fragmentos reutilizables

## 3. `partials/header.php`

Este archivo sirve como plantilla base para todas las vistas internas del sistema.

### ¿Qué hace?

- inicia la sesión si hace falta,
- detecta si hay un usuario autenticado,
- decide si mostrar sidebar o header público,
- define el título de la página,
- incluye el CSS principal,
- monta la estructura general del HTML.

### Lógica clave

```php
$haySesion = isset($_SESSION['usuario_id']);
$rolId = $haySesion ? (int) $_SESSION['rol_id'] : null;
```

Esto permite cambiar la interfaz según el rol del usuario.

### Sidebar

Cuando existe sesión, se muestra una barra lateral con enlaces:

- Inicio (`/dashboard`)
- Mascotas (`/mascota`)
- Citas (`/cita`)
- Usuarios (`/usuario`) si es administrador

### Modo público

Si no hay sesión, se muestra un header simple y el usuario ve el Login o Registro.

## 4. `partials/footer.php`

Es el cierre de la estructura HTML.

Hace dos cosas importantes:

- cierra los tags `</main>` y `</div>` del layout,
- carga el archivo `/js/app.js` cuando la sesión existe.

También evita cargar contenido extra si la petición es AJAX.

## 5. Cómo las vistas reciben datos

Los controladores pasan datos a la vista con:

```php
$this->vista('dashboard/index', [
    'nombre' => $nombre,
    'rolId' => $rolId,
    'mascotas' => $mascotas,
]);
```

Luego, dentro de la vista, esos datos quedan en variables normales de PHP.

Por ejemplo:

```php
<?php foreach ($mascotas as $mascota): ?>
    <li><?= htmlspecialchars($mascota['nombre']) ?></li>
<?php endforeach; ?>
```

Esto es posible porque `Controller::vista()` hace `extract($datos)`.

## 6. `Views/auth/login.php`

Es la pantalla de ingreso.

### Elementos principales

- formulario con `method="POST"` y action `/auth/login`
- campo correo
- campo contraseña
- enlace a registro

### Validación visual

Si hay error, se muestra:

```php
<?php if (!empty($error)): ?>
    <p style="color:red;">...</p>
<?php endif; ?>
```

Esto permite mostrar mensajes de acceso denegado o credenciales válidas.

## 7. `Views/dashboard/index.php`

Es el panel principal.

### Según el rol del usuario:

#### DueñoMascota

- muestra sus mascotas,
- muestra próximas citas,
- enlaza para ver historial o agendar citas.

#### Veterinario

- muestra la agenda asignada de citas.

#### Recepcionista o Administrador

- muestra resumen del sistema,
- cantidad de mascotas activas,
- cantidad de usuarios,
- agenda general.

## 8. `Views/mascotas/crear.php`

Formulario para crear una mascota.

### Campos:

- nombre,
- especie,
- raza,
- fecha de nacimiento,
- sexo,
- peso.

Se envía a:

```php
<form action="/mascota/crear" method="POST">
```

y el `MascotaController` procesa esa información.

## 9. `Views/citas/crear.php`

Es el formulario para agendar una cita.

### Campos típicos:

- mascota_id
- veterinario_id
- fecha
- hora
- tipo_consulta

### Importancia

El controlador revisa si el horario del veterinario está libre antes de guardar la cita.

Si no lo está, la vista recibe:

- `error`
- `sugerencias`

para mostrar alternativas de horario.

## 10. Archivos `editar.php` y `reagendar.php`

Estos archivos son muy similares a los de crear, pero se usan para:

- modificar una mascota,
- reagendar una cita ya existente,
- mostrar una instancia actual con información previa.

Se cargan con datos ya existentes y usan valores iniciales (`value`, `selected`) para facilitar edición.

## 11. ¿Cómo se organiza la presentación?

La estructura se basa en una lógica muy simple:

- cada formulario tiene su acción del controlador,
- cada vista se encarga de leer variables entregadas por el controlador,
- el HTML se combina con PHP para generar contenido dinámico,
- la parte visual se centraliza con `header` y `footer`.

## 12. Diferencia entre lógica y presentación

La vista no debe hacer consultas ni decidir reglas de negocio. Solo debe:

- mostrar contenido,
- renderizar HTML,
- capturar datos del usuario mediante formularios,
- mostrar mensajes de error o éxito.

La lógica está en controladores y modelos; la vista solo presenta los resultados.

## 13. Seguridad en las vistas

Se usa `htmlspecialchars()` para evitar XSS al imprimir contenido proveniente de la base de datos o del usuario:

```php
<?= htmlspecialchars($mascota['nombre']) ?>
```

Esto convierte caracteres peligrosos en texto seguro para la salida HTML.

## 14. Resumen

Las vistas son la interfaz del sistema y se conectan con el resto de la arquitectura así:

- `Public/index.php` decide la ruta,
- `Controller` prepara datos,
- el controlador busca información en los modelos,
- la vista recibe esos datos,
- el usuario interactúa con formularios y botones,
- la información vuelve al controlador y luego al modelo.

En otras palabras, las vistas son la cara visible del sistema, pero la lógica real vive fuera de ellas.
