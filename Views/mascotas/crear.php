<?php
$tituloPagina = 'Registrar Mascota';
require __DIR__ . '/../partials/header.php';
?>

<h1>Registrar Mascota</h1>

<div class="tarjeta" data-etiqueta="Nueva mascota">
    <?php if (!empty($error)): ?>
        <p class="mensaje mensaje-error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="/mascota/crear" method="POST">
        <label>Nombre:
            <input type="text" name="nombre" required>
        </label>

        <label>Especie:
            <input type="text" name="especie" required>
        </label>

        <label>Raza:
            <input type="text" name="raza" required>
        </label>

        <label>Fecha de nacimiento:
            <input type="date" name="fecha_nacimiento" required>
        </label>

        <label>Sexo:
            <select name="sexo" required>
                <option value="">-- Selecciona --</option>
                <option value="Macho">Macho</option>
                <option value="Hembra">Hembra</option>
            </select>
        </label>

        <label>Peso (kg):
            <input type="number" name="peso" step="0.01" min="0" required>
        </label>

        <button type="submit">Registrar</button>
        <a href="/mascota" class="boton" style="background:var(--color-borde); color:var(--color-tinta);">Cancelar</a>
    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>