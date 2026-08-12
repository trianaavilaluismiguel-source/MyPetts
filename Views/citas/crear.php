<?php
$tituloPagina = 'Agendar Cita';
require __DIR__ . '/../partials/header.php';
?>

<?php
// Valores por defecto para evitar notices
$mascotas = $mascotas ?? [];
$veterinarios = $veterinarios ?? [];
$error = $error ?? null;
$sugerencias = $sugerencias ?? [];
?>

<h1>Agendar Cita</h1>

<div class="tarjeta" data-etiqueta="Nueva cita">
    <?php if (!empty($error)): ?>
        <p class="mensaje mensaje-error"><?= htmlspecialchars($error) ?></p>

        <?php if (!empty($sugerencias)): ?>
            <p>Próximos horarios disponibles con ese veterinario:</p>
            <ul>
                <?php foreach ($sugerencias as $s): ?>
                    <li><?= htmlspecialchars($s['fecha']) ?> a las <?= htmlspecialchars(substr($s['hora'], 0, 5)) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>

    <form action="/cita/crear" method="POST">
        <label>Mascota:
            <select name="mascota_id" required>
                <option value="">-- Selecciona --</option>
                <?php foreach ($mascotas as $mascota): ?>
                    <option value="<?= $mascota['id'] ?>"><?= htmlspecialchars($mascota['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>Veterinario:
            <select name="veterinario_id" required>
                <option value="">-- Selecciona --</option>
                <?php foreach ($veterinarios as $veterinario): ?>
                    <option value="<?= $veterinario['id'] ?>"><?= htmlspecialchars($veterinario['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>Fecha:
            <input type="date" name="fecha" required>
        </label>

        <label>Hora:
            <select name="hora" required>
                <option value="">-- Selecciona --</option>
                <option value="08:00:00">8:00 AM</option>
                <option value="09:00:00">9:00 AM</option>
                <option value="10:00:00">10:00 AM</option>
                <option value="11:00:00">11:00 AM</option>
                <option value="12:00:00">12:00 PM</option>
                <option value="13:00:00">1:00 PM</option>
                <option value="14:00:00">2:00 PM</option>
                <option value="15:00:00">3:00 PM</option>
                <option value="16:00:00">4:00 PM</option>
                <option value="17:00:00">5:00 PM</option>
            </select>
        </label>

        <label>Tipo de consulta:
            <input type="text" name="tipo_consulta" required>
        </label>

        <button type="submit">Agendar</button>
        <a href="/cita" class="boton" style="background:var(--color-borde); color:var(--color-tinta);">Cancelar</a>
    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>