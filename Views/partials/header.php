<?php
// $tituloPagina lo define cada vista antes de incluir este header (opcional)
$tituloPagina = $tituloPagina ?? 'MyPetts';

// Si hay sesión activa, la barra de navegación cambia según el rol
$haySesion = isset($_SESSION['usuario_id']);
$esAdministrador = $haySesion && $_SESSION['rol_id'] == 1;
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

<header class="barra-nav">
    <span class="marca">🐾 MyPetts</span>
    <?php if ($haySesion): ?>
    <nav>
        <a href="/dashboard">Inicio</a>
        <a href="/mascota">Mascotas</a>
        <a href="/cita">Citas</a>
        <?php if ($esAdministrador): ?>
        <a href="/usuario">Usuarios</a>
        <?php endif; ?>
        <a href="/auth/logout">Cerrar sesión</a>
    </nav>
    <?php endif; ?>
</header>

<main class="contenedor">