<?php
$tituloPagina = 'Reagendar Cita';
require __DIR__ . '/../partials/header.php';
?>

<h1>Reagendar Cita</h1>

<div class="tarjeta" data-etiqueta="Cita actual">
    <p>
        <?= htmlspecialchars($cita['fecha'] ?? '') ?> a las <?= htmlspecialchars(substr($cita['hora'] ?? '', 0, 5)) ?>
        (<?= htmlspecialchars($cita['tipo_consulta'] ?? '') ?>)
    </p>

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

    <form action="/cita/reagendar/<?= (int) ($cita['id'] ?? 0) ?>" method="POST">
        <label>Nueva fecha:
            <input type="date" name="fecha" required>
        </label>

        <label>Nueva hora:
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

        <button type="submit">Confirmar reagendamiento</button>
        <a href="/cita" class="boton" style="background:var(--color-borde); color:var(--color-tinta);">Cancelar</a>
    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>