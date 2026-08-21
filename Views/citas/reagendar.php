<?php
$tituloPagina = 'Reagendar Cita';
require __DIR__ . '/../partials/header.php';
?>

<a href="/cita" class="btn-volver"><span>←</span> Volver a Citas</a>

<header class="section-header">
    <div>
        <h1>Reagendar Cita</h1>
    </div>
</header>

<?php if (!empty($error)): ?>
    <div class="auth-alert" style="margin-bottom: 20px;">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<?php if (!empty($sugerencias)): ?>
    <div class="sugerencias-horario">
        <p>Horarios disponibles más cercanos con ese veterinario:</p>
        <div class="sugerencias-lista">
            <?php foreach ($sugerencias as $s): ?>
                <span class="chip-sugerencia">
                    <?= htmlspecialchars(date('d/m', strtotime($s['fecha']))) ?> — <?= htmlspecialchars(date('h:i A', strtotime($s['hora']))) ?>
                </span>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div class="tarjeta form-container" data-etiqueta="REAGENDAR CITA">
    <div class="info-cita-box">
        <small>Cita Actual</small>
        <strong>📅 <?= htmlspecialchars(date('d/m/Y', strtotime($cita['fecha'] ?? 'now'))) ?> - <?= htmlspecialchars(date('h:i A', strtotime($cita['hora'] ?? 'now'))) ?></strong>
        <p>Motivo: <?= htmlspecialchars($cita['tipo_consulta'] ?? 'Consulta médica') ?></p>
    </div>

    <form action="/cita/reagendar/<?= (int) $cita['id'] ?>" method="POST" style="display: flex; flex-direction: column; gap: 18px;">
        <div class="form-grid">
            <div>
                <label for="fecha" class="campo-label">Nueva Fecha *</label>
                <input type="date" id="fecha" name="fecha" class="input-custom" required>
            </div>

            <div>
                <label for="hora" class="campo-label">Nueva Hora *</label>
                <select id="hora" name="hora" class="select-custom" required>
                    <option value="">-- Selecciona --</option>
                    <option value="08:00:00">08:00 AM</option>
                    <option value="09:00:00">09:00 AM</option>
                    <option value="10:00:00">10:00 AM</option>
                    <option value="11:00:00">11:00 AM</option>
                    <option value="14:00:00">02:00 PM</option>
                    <option value="15:00:00">03:00 PM</option>
                    <option value="16:00:00">04:00 PM</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn">Confirmar reagendamiento</button>
            <a href="/cita" class="btn-cancelar">Cancelar</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>