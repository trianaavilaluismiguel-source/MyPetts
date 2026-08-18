# Documentación de Public

## 1. ¿Qué es Public?

La carpeta `Public` es la capa de entrada del sistema. Aquí vive el punto de acceso web que recibe todas las peticiones del navegador y decide qué controlador debe ejecutarse.

La estructura principal es:

- `Public/index.php` – archivo de arranque.
- `Public/css/style.css` – estilos del sistema.
- `Public/js/app.js` – lógica del frontend ligera.

## 2. Flujo de una petición HTTP

Cuando el usuario entra por ejemplo a:

- `/auth`
- `/dashboard`
- `/mascota`
- `/cita`

la petición llega a Apache/Nginx y termina cargando `Public/index.php` (según la configuración del servidor web).

El flujo real es:

1. El navegador solicita una URL.
2. El servidor ejecuta `Public/index.php`.
3. `index.php` interpreta la URL.
4. Carga el controlador correspondiente.
5. Ejecuta la acción (`index`, `crear`, `editar`, etc.).
6. El controlador usa modelos para consultar o guardar datos.
7. El controlador renderiza la vista y devuelve HTML.

## 3. `Public/index.php`: punto de entrada

El archivo principal hace lo siguiente:

### 3.1 Carga la clase base del controlador

```php
require_once __DIR__ . '/../Controllers/Controller.php';
```

Esto permite que el proyecto pueda usar la clase abstracta `Controller` para reutilizar métodos comunes.

### 3.2 Registro de autoload simple

```php
spl_autoload_register(function ($clase) {
    $rutas = [__DIR__ . "/../Controllers/$clase.php", __DIR__ . "/../Models/$clase.php"];
    foreach ($rutas as $ruta) {
        if (file_exists($ruta)) {
            require_once $ruta;
            return;
        }
    }
});
```

Este bloque carga automáticamente los archivos `Controllers/NombreController.php` o `Models/Nombre.php` cuando se instancia una clase.

Es decir, no hace falta hacer `require_once` manual en cada controladores con un autoload básico.

### 3.3 Lectura de la URL

```php
$url = trim($_SERVER['REQUEST_URI'], '/');
$url = explode('?', $url)[0];
$partes = $url === '' ? [] : explode('/', $url);
```

Esto limpia la URL y separa cada segmento:

- `/auth/login` → `['auth', 'login']`
- `/mascota/crear` → `['mascota', 'crear']`
- `/cita/mostrarCrear` → `['cita', 'mostrarCrear']`

### 3.4 Resolución del controlador

```php
$nombreControlador = !empty($partes[0]) ? ucfirst($partes[0]) . 'Controller' : 'AuthController';
$accion = $partes[1] ?? 'index';
$parametro = $partes[2] ?? null;
```

Reglas:

- Si la URL empieza con `auth`, se usa `AuthController`.
- Si la URL empieza con `mascota`, se usa `MascotaController`.
- Si la URL empieza con `dashboard`, se usa `DashboardController`.
- Si no hay controlador, usa `AuthController` por defecto.

La acción también se toma del segundo segmento:

- `/auth` → `index`
- `/cita/mostrarCrear` → `mostrarCrear`
- `/usuario/editar/5` → `editar(5)`

### 3.5 Validación de existencia

```php
if (!class_exists($nombreControlador)) {
    http_response_code(404);
    die("Página no encontrada.");
}
```

Si el nombre del controlador no existe, responde `404`.

### 3.6 Instanciación y llamada

```php
$controlador = new $nombreControlador();

if (!method_exists($controlador, $accion)) {
    http_response_code(404);
    die("Acción no encontrada.");
}

$parametro !== null ? $controlador->$accion($parametro) : $controlador->$accion();
```

Este bloque es el núcleo del routing.

Si el controlador y la acción existen, se ejecuta la lógica. Si no, se devuelve un error 404.

## 4. Ejemplo de URL a controlador

- URL: `/auth/login`
- Controlador: `AuthController`
- Acción: `login`

- URL: `/mascota/mostrarCrear`
- Controlador: `MascotaController`
- Acción: `mostrarCrear`

- URL: `/cita/reagendar/12`
- Controlador: `CitaController`
- Acción: `reagendar(12)`

## 5. Relación con el resto del proyecto

`Public/index.php` no hace ni consultas ni lógica empresarial. Solo actúa como:

- distribuidor de peticiones,
- interpretador de rutas,
- invocador de controladores,
- responsable de avisar cuando una ruta es inválida.

La lógica real está en:

- `Controllers/` – decisiones y validaciones,
- `Models/` – acceso a la base de datos,
- `Views/` – presentación visual.

## 6. Cómo se conecta con CSS y JS

La vista usa rutas como:

```php
<link rel="stylesheet" href="/css/style.css">
<script src="/js/app.js" defer></script>
```

Eso implica que el servidor web debe estar configurado para servir la carpeta `Public` como raíz pública de la aplicación. En este proyecto, el acceso directo a archivos de frontend se hace desde esa carpeta.

## 7. Resumen del patrón

El patrón principal es MVC simplificado:

- `Public` = entrada
- `Controllers` = controladores
- `Models` = lógica de datos
- `Views` = interfaz

Esto permite separar responsabilidades y hace que el sistema sea más ordenado y mantenible.

## 8. Importante

Aunque el sistema parece pequeño, el enrutamiento del proyecto está bien estructurado para crecer: cada entidad tiene su propio controlador, su propio modelo, sus acciones y sus vistas.

Eso facilita la escalabilidad y la organización del código.
