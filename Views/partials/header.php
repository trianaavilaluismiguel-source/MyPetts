<?php
$tituloPagina = $tituloPagina ?? 'MyPetts';

$haySesion = isset($_SESSION['usuario_id']);
$rolId = $haySesion ? (int) $_SESSION['rol_id'] : null;
$esAdministrador = $rolId === 1;

$moduloActual = explode('/', trim($_SERVER['REQUEST_URI'] ?? '', '/'))[0] ?? '';
$moduloActual = explode('?', $moduloActual)[0];

// Si la petición viene de nuestro propio JS (navegación tipo SPA), el navegador
// ya tiene el <head> y el sidebar; solo necesitamos el contenido interno.
$esPeticionAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
if ($esPeticionAjax) {
    return;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($tituloPagina) ?> - MyPetts</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/style.css">
</head>
<body>

<?php if ($haySesion): ?>
<div class="app-layout">
    <aside class="barra-lateral">
        <div class="marca">🐾 MyPetts</div>
        <nav>
            <a href="/dashboard" class="<?= $moduloActual === 'dashboard' ? 'activo' : '' ?>">
                <span class="icono">🏠</span><span>Inicio</span>
            </a>
            <a href="/mascota" class="<?= $moduloActual === 'mascota' ? 'activo' : '' ?>">
                <span class="icono">🐾</span><span>Mascotas</span>
            </a>
            <a href="/cita" class="<?= $moduloActual === 'cita' ? 'activo' : '' ?>">
                <span class="icono">📅</span><span>Citas</span>
            </a>
            <?php if ($esAdministrador): ?>
            <a href="/usuario" class="<?= $moduloActual === 'usuario' ? 'activo' : '' ?>">
                <span class="icono">👥</span><span>Usuarios</span>
            </a>
            <?php endif; ?>
        </nav>
        <a href="/auth/logout" class="cerrar-sesion" data-recarga-completa>
            <span class="icono">🚪</span><span>Cerrar sesión</span>
        </a>
    </aside>

    <main id="contenido-principal" class="contenedor">
<?php else: ?>
<header class="barra-nav">
    <span class="marca">🐾 MyPetts</span>
</header>

<main class="contenedor">
<?php endif; ?>