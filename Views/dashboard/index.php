<?php
$tituloPagina = 'Inicio';
require __DIR__ . '/../partials/header.php';
?>

<?php
$usuario = $usuario ?? [];
$nombre = $nombre ?? ($usuario['nombre'] ?? 'Usuario');
$rolId = (int) ($rolId ?? $usuario['rol_id'] ?? 0);

$mascotas = $mascotas ?? [];
$citas = $citas ?? [];
$totalMascotas = $totalMascotas ?? 0;
$totalUsuarios = $totalUsuarios ?? 0;

$nombresRoles = [
    1 => 'Administrador',
    2 => 'Veterinario',
    3 => 'Recepcionista',
    4 => 'Dueño de mascota',
];
$etiquetaRol = $nombresRoles[$rolId] ?? '';

$icono = fn(string $e) => (strtolower($e) === 'gato') ? '🐱' : ((strtolower($e) === 'pajaro') ? '🦜' : '🐶');
?>

<div class="welcome-banner">
    <h1>¡Bienvenido, <?= htmlspecialchars($nombre) ?>!
        <?php if ($etiquetaRol !== ''): ?>
            <span class="rol-chip"><?= htmlspecialchars($etiquetaRol) ?></span>
        <?php endif; ?>
    </h1>
    <p>
        <?php if ($rolId === 4): ?>
            Aquí tienes un resumen rápido del estado y las actividades de tus mascotas.
        <?php elseif ($rolId === 2): ?>
            Este es tu resumen de consultas próximas asignadas.
        <?php elseif ($rolId === 3): ?>
            Panel general de la clínica: mascotas registradas, usuarios y agenda del día.
        <?php elseif ($rolId === 1): ?>
            Panel de administración: estadísticas generales del sistema y agenda de la clínica.
        <?php else: ?>
            Aquí tienes un resumen rápido de tu actividad en MyPetts.
        <?php endif; ?>
    </p>
</div>

<?php if ($rolId === 4): ?>
    <!-- ===== DUEÑO DE MASCOTA ===== -->
    <div class="quick-actions">
        <a href="/mascota/mostrarCrear" class="action-card">
            <div class="action-icon">🐾</div>
            <div class="action-info">
                <h3>Registrar Mascota</h3>
                <p>Añade un nuevo integrante</p>
            </div>
        </a>
        <a href="/cita/mostrarCrear" class="action-card">
            <div class="action-icon">📅</div>
            <div class="action-info">
                <h3>Agendar Cita</h3>
                <p>Programa una consulta médica</p>
            </div>
        </a>
    </div>

    <div class="dashboard-grid">
        <div class="dash-card">
            <div class="dash-card-header">
                <h2>🐾 Mis Mascotas</h2>
                <a href="/mascota" class="dash-link">Ver todas →</a>
            </div>
            <div class="dash-list">
                <?php if (!empty($mascotas)): ?>
                    <?php foreach (array_slice($mascotas, 0, 3) as $m): ?>
                        <div class="list-item">
                            <div class="item-main">
                                <div class="item-avatar"><?= $icono($m['especie'] ?? '') ?></div>
                                <div class="item-details">
                                    <h4><?= htmlspecialchars($m['nombre']) ?></h4>
                                    <p><?= htmlspecialchars($m['especie']) ?><?= !empty($m['raza']) ? ' • ' . htmlspecialchars($m['raza']) : '' ?></p>
                                </div>
                            </div>
                            <span class="status-pill"><?= htmlspecialchars(str_replace('_', ' ', $m['estado_salud'] ?? 'Al día')) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty-state">No tienes mascotas registradas aún.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="dash-card">
            <div class="dash-card-header">
                <h2>📅 Próximas Citas</h2>
                <a href="/cita" class="dash-link">Ver agenda →</a>
            </div>
            <div class="dash-list">
                <?php if (!empty($citas)): ?>
                    <?php foreach (array_slice($citas, 0, 3) as $c): ?>
                        <div class="list-item">
                            <div class="item-main">
                                <div class="item-avatar">🏥</div>
                                <div class="item-details">
                                    <h4><?= htmlspecialchars($c['nombre_mascota'] ?? 'Mascota') ?></h4>
                                    <p>Con Dr(a). <?= htmlspecialchars($c['nombre_veterinario'] ?? 'veterinario') ?></p>
                                </div>
                            </div>
                            <div class="badge-date">
                                <?= htmlspecialchars(date('d/m', strtotime($c['fecha'] ?? 'now'))) ?> • <?= htmlspecialchars(date('h:i A', strtotime($c['hora'] ?? 'now'))) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty-state">No tienes citas agendadas próximamente.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php elseif ($rolId === 2): ?>
    <!-- ===== VETERINARIO ===== -->
    <div class="dashboard-grid">
        <div class="dash-card">
            <div class="dash-card-header">
                <h2>📅 Mi Agenda</h2>
                <a href="/cita" class="dash-link">Ver todas →</a>
            </div>
            <div class="dash-list">
                <?php if (!empty($citas)): ?>
                    <?php foreach ($citas as $c): ?>
                        <div class="list-item">
                            <div class="item-main">
                                <div class="item-avatar">🏥</div>
                                <div class="item-details">
                                    <h4><?= htmlspecialchars($c['nombre_mascota'] ?? 'Mascota') ?></h4>
                                    <p>Dueño: <?= htmlspecialchars($c['nombre_dueno'] ?? '—') ?></p>
                                </div>
                            </div>
                            <div class="badge-date">
                                <?= htmlspecialchars(date('d/m', strtotime($c['fecha'] ?? 'now'))) ?> • <?= htmlspecialchars(date('h:i A', strtotime($c['hora'] ?? 'now'))) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty-state">No tienes consultas agendadas próximamente.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- ===== ADMINISTRADOR / RECEPCIONISTA ===== -->
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon">🐾</div>
            <div>
                <p class="stat-number"><?= (int) $totalMascotas ?></p>
                <p class="stat-label">Mascotas activas</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div>
                <p class="stat-number"><?= count($citas) ?></p>
                <p class="stat-label">Citas próximas</p>
            </div>
        </div>
        <?php if ($rolId === 1): ?>
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div>
                    <p class="stat-number"><?= (int) $totalUsuarios ?></p>
                    <p class="stat-label">Usuarios registrados</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="quick-actions">
        <a href="/cita/mostrarCrear" class="action-card">
            <div class="action-icon">📅</div>
            <div class="action-info">
                <h3>Agendar Cita</h3>
                <p>Programa una consulta médica</p>
            </div>
        </a>
        <?php if ($rolId === 1): ?>
            <a href="/usuario/mostrarCrear" class="action-card">
                <div class="action-icon">👥</div>
                <div class="action-info">
                    <h3>Nuevo Usuario</h3>
                    <p>Registra personal o dueños</p>
                </div>
            </a>
        <?php endif; ?>
    </div>

    <div class="dashboard-grid">
        <div class="dash-card">
            <div class="dash-card-header">
                <h2>📅 Agenda de la Clínica</h2>
                <a href="/cita" class="dash-link">Ver todas →</a>
            </div>
            <div class="dash-list">
                <?php if (!empty($citas)): ?>
                    <?php foreach (array_slice($citas, 0, 5) as $c): ?>
                        <div class="list-item">
                            <div class="item-main">
                                <div class="item-avatar">🏥</div>
                                <div class="item-details">
                                    <h4><?= htmlspecialchars($c['nombre_mascota'] ?? 'Mascota') ?></h4>
                                    <p>Dr(a). <?= htmlspecialchars($c['nombre_veterinario'] ?? '—') ?></p>
                                </div>
                            </div>
                            <div class="badge-date">
                                <?= htmlspecialchars(date('d/m', strtotime($c['fecha'] ?? 'now'))) ?> • <?= htmlspecialchars(date('h:i A', strtotime($c['hora'] ?? 'now'))) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty-state">No hay citas agendadas próximamente.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>