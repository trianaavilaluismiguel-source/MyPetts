# Documentación de Views

## 1. ¿Qué es una vista?

Cada archivo dentro de `Views/` genera el HTML que ve el usuario. La organización del proyecto es por módulos: `auth`, `dashboard`, `mascotas`, `citas`, `usuarios`, `historial`, `vacunas` y `partials`.

## 2. Estructura actual del sistema

La capa de vistas usa dos piezas principales:

- `Views/partials/header.php`: abre el documento, carga CSS y decide el layout público o autenticado.
- `Views/partials/footer.php`: cierra los tags HTML y carga JS cuando corresponde.

No existe un layout central global como `Views/layouts/main.php` en la versión actual. Es un residuo viejo y fue eliminado para evitar conflicto doble.

## 3. `header.php`

Este archivo:

- define `$tituloPagina`
- detecta si hay sesión
- muestra sidebar al usuario autenticado
- muestra un header simple para login/registro
- incluye la hoja de estilos `/css/style.css`

Si la sesión existe, se renderiza la barra lateral con:

- Dashboard
- Mascotas
- Citas
- Usuarios (si aplica)

## 4. `footer.php`

Cierra el HTML y, si hay sesión, carga `/js/app.js`.

## 5. Cómo reciben datos

Los controladores pasan variables con `vista()` y la vista las usa directamente:

```php
$this->vista('citas/index', ['citas' => $citas, 'mensaje' => $mensaje]);
```

Y dentro de la vista:

```php
<?php foreach ($citas as $cita): ?>
    <td><?= htmlspecialchars($cita['nombre_mascota']) ?></td>
<?php endforeach; ?>
```

## 6. Vistas de autenticación

### `Views/auth/login.php`

Muestra el formulario para iniciar sesión con:

- correo
- contraseña
- enlace de recuperación bajo el campo de contraseña: “¿Olvidaste tu contraseña?”
- enlace a registro
- mensajes de error o éxito visuales

La vista usa un panel centralizado tipo ventana de escritorio, con tarjeta de ancho moderado y estilo oscuro consistente con la marca.

### `Views/auth/registro.php`

Muestra el formulario de alta con:

- nombre
- correo
- teléfono
- contraseña
- confirmación

La distribución está organizada en dos columnas para pantallas de escritorio, manteniendo una lectura clara y una apariencia más orientada a PC, y se mantiene responsive en móviles con una columna única.

## 7. Vistas de módulos

Cada módulo tiene una vista principal y, cuando aplica, sus vistas de creación/edición:

- `Views/dashboard/index.php`
- `Views/mascotas/index.php`, `crear.php`, `editar.php`
- `Views/citas/index.php`, `crear.php`, `reagendar.php`
- `Views/usuarios/index.php`, `crear.php`, `editar.php`
- `Views/historial/index.php`, `crear.php`, `editar.php`
- `Views/vacunas/index.php`, `crear.php`

## 8. Reglas de presentación

- No hacer consultas en la vista.
- No mezclar lógica de negocio con HTML.
- Mostrar mensajes con clases de estilo (`mensaje`, `mensaje-error`, `mensaje-exito`).
- Escapar todos los valores con `htmlspecialchars()` antes de imprimirlos.

## 9. Revisión final

La vista debe ser declarativa y clara: recibe datos, los recorre, y los presenta. La lógica de flujo, validación y permisos pertenece al controlador y al modelo.

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
