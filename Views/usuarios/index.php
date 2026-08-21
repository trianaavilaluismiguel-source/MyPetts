<?php
$tituloPagina = 'Gestión de Usuarios';
require __DIR__ . '/../partials/header.php';
?>

<a href="/dashboard" class="btn-volver">
    <span>←</span> Volver al Dashboard
</a>

<header class="section-header">
    <div>
        <h1>Gestión de Usuarios</h1>
        <p>Administra el personal y los dueños registrados en el sistema.</p>
    </div>
    <a href="/usuario/mostrarCrear" class="btn">+ Nuevo Usuario</a>
</header>

<?php if (!empty($mensaje)): ?>
    <div class="auth-alert auth-success">
        <?= htmlspecialchars($mensaje) ?>
    </div>
<?php endif; ?>

<div class="tarjeta" data-etiqueta="USUARIOS">

    <div class="tabla-contenedor">
        <?php if (!empty($usuarios)): ?>
            <table class="tabla-custom">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($usuario['nombre']) ?></strong></td>
                            <td><?= htmlspecialchars($usuario['correo']) ?></td>
                            <td><?= htmlspecialchars($usuario['telefono']) ?></td>
                            <td><?= htmlspecialchars($usuario['nombre_rol']) ?></td>
                            <td>
                                <span class="badge <?= $usuario['activo'] ? 'badge-agendada' : 'badge-cancelada' ?>">
                                    <?= $usuario['activo'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td>
                                <div class="acciones-cell">
                                    <a href="/usuario/mostrarEditar/<?= (int) $usuario['id'] ?>" class="btn-editar">Editar</a>
                                    <a href="/usuario/resetearContrasena/<?= (int) $usuario['id'] ?>"
                                       class="btn-reagendar"
                                       onclick="return confirm('¿Restablecer la contraseña de este usuario?')">
                                        Resetear
                                    </a>
                                    <a href="/usuario/cambiarEstado/<?= (int) $usuario['id'] ?>"
                                       class="btn-baja"
                                       onclick="return confirm('¿Cambiar el estado de este usuario?')">
                                        <?= $usuario['activo'] ? 'Desactivar' : 'Activar' ?>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="empty-state">No hay usuarios registrados.</p>
        <?php endif; ?>
    </div>

</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>