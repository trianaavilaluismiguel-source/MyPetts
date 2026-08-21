<?php
$tituloPagina = 'Agendar Cita';
require __DIR__ . '/../partials/header.php';
?>

<a href="/cita" class="btn-volver">
    <span>←</span> Volver a Citas
</a>

<header class="section-header">
    <div>
        <h1>Agendar Cita</h1>
        <p>Selecciona los detalles para agendar la atención veterinaria de tu mascota.</p>
    </div>
</header>

<?php if (!empty($error)): ?>
    <div class="auth-alert">
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

<div class="tarjeta form-container" data-etiqueta="NUEVA CITA">

    <form action="/cita/crear" method="POST">
        <div class="form-grid">

            <div>
                <label class="campo-label" for="mascota_id">Mascota *</label>
                <select id="mascota_id" name="mascota_id" class="select-custom" required>
                    <option value="">-- Selecciona --</option>
                    <?php if (!empty($mascotas)): ?>
                        <?php foreach ($mascotas as $m): ?>
                            <option value="<?= (int) $m['id'] ?>"><?= htmlspecialchars($m['nombre']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div>
                <label class="campo-label" for="veterinario_id">Veterinario *</label>
                <select id="veterinario_id" name="veterinario_id" class="select-custom" required>
                    <option value="">-- Selecciona --</option>
                    <?php if (!empty($veterinarios)): ?>
                        <?php foreach ($veterinarios as $v): ?>
                            <option value="<?= (int) $v['id'] ?>"><?= htmlspecialchars($v['nombre']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div>
                <label class="campo-label" for="fecha">Fecha *</label>
                <input type="date" id="fecha" name="fecha" class="input-custom" required>
            </div>

            <div>
                <label class="campo-label" for="hora">Hora *</label>
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

            <div class="form-group-full">
                <label class="campo-label" for="tipo_consulta">Tipo de consulta *</label>
                <input type="text" id="tipo_consulta" name="tipo_consulta" class="input-custom" placeholder="Ej. Chequeo general, Vacunación, Urgencia..." required>
            </div>

        </div>

        <div class="form-actions">
            <button type="submit" class="btn">Agendar Cita</button>
            <a href="/cita" class="btn-cancelar">Cancelar</a>
        </div>
    </form>

</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>