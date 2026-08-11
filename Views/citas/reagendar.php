<h1>Reagendar Cita</h1>

<p>
    Cita actual: <?= htmlspecialchars($cita['fecha']) ?> a las <?= htmlspecialchars(substr($cita['hora'], 0, 5)) ?>
    (<?= htmlspecialchars($cita['tipo_consulta']) ?>)
</p>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>

    <?php if (!empty($sugerencias)): ?>
        <p>Próximos horarios disponibles con ese veterinario:</p>
        <ul>
            <?php foreach ($sugerencias as $s): ?>
                <li><?= htmlspecialchars($s['fecha']) ?> a las <?= htmlspecialchars(substr($s['hora'], 0, 5)) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php endif; ?>

<form action="/cita/reagendar/<?= $cita['id'] ?>" method="POST">
    <label>Nueva fecha:
        <input type="date" name="fecha" required>
    </label><br>

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
    </label><br>

    <button type="submit">Confirmar reagendamiento</button>
</form>

<a href="/cita">Cancelar y volver a la lista</a>