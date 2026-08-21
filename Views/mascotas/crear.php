<?php
$tituloPagina = 'Registrar Mascota';
require __DIR__ . '/../partials/header.php';
?>

<a href="/mascota" class="btn-volver"><span>←</span> Volver a Mascotas</a>

<header class="section-header">
    <div>
        <h1>Registrar Mascota</h1>
        <p>Ingresa los detalles de tu nuevo integrante para llevar su expediente.</p>
    </div>
</header>

<?php if (!empty($error)): ?>
    <div class="auth-alert" style="margin-bottom: 20px; max-width: 650px;">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="tarjeta form-container" data-etiqueta="NUEVA MASCOTA">
    <form action="/mascota/crear" method="POST">
        <div class="form-grid">
            <div class="form-group-full">
                <label class="campo-label" for="nombre">Nombre *</label>
                <input type="text" id="nombre" name="nombre" class="input-custom" placeholder="Ej. Firulais" required>
            </div>

            <div>
                <label class="campo-label" for="especie">Especie *</label>
                <input type="text" id="especie" name="especie" class="input-custom" placeholder="Ej. Perro, Gato, Pájaro" required>
            </div>

            <div>
                <label class="campo-label" for="raza">Raza</label>
                <input type="text" id="raza" name="raza" class="input-custom" placeholder="Ej. Labrador">
            </div>

            <div>
                <label class="campo-label" for="fecha_nacimiento">Fecha de nacimiento *</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="input-custom" required>
            </div>

            <div>
                <label class="campo-label" for="sexo">Sexo</label>
                <select id="sexo" name="sexo" class="select-custom">
                    <option value="">-- Selecciona --</option>
                    <option value="Macho">Macho</option>
                    <option value="Hembra">Hembra</option>
                </select>
            </div>

            <div class="form-group-full">
                <label class="campo-label" for="peso">Peso (kg)</label>
                <input type="number" step="0.1" id="peso" name="peso" class="input-custom" placeholder="Ej. 8.5">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn">Registrar Mascota</button>
            <a href="/mascota" class="btn-cancelar">Cancelar</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>