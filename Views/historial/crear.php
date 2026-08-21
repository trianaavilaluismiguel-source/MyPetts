<?php
$mascota = $mascota ?? ['id' => 0, 'nombre' => ''];
$error = $error ?? null;

$tituloPagina = 'Registrar Entrada Clínica';
require __DIR__ . '/../partials/header.php';
?>

<h1>Registrar Entrada Clínica — <?= htmlspecialchars($mascota['nombre']) ?></h1>

<div class="tarjeta" data-etiqueta="Nueva entrada">
    <?php if (!empty($error)): ?>
        <p class="mensaje mensaje-error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="/historial/crear/<?= (int) $mascota['id'] ?>" method="POST">
        <label>Motivo de consulta:
            <input type="text" name="motivo_consulta" required>
        </label>

        <label>Diagnóstico:
            <textarea name="diagnostico" rows="4" required></textarea>
        </label>

        <label>Tratamiento:
            <textarea name="tratamiento" rows="4" required></textarea>
        </label>

        <label>Observaciones (opcional):
            <textarea name="observaciones" rows="3"></textarea>
        </label>

        <button type="submit">Guardar</button>
        <a href="/historial/verHistorial/<?= (int) $mascota['id'] ?>" class="boton inline-cancel">Cancelar</a>
    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>