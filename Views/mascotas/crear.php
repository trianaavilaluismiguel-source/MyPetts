<h1>Registrar Mascota</h1>

<?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form action="/mascota/crear" method="POST">
    <label>Nombre:
        <input type="text" name="nombre" required>
    </label><br>

    <label>Especie:
        <input type="text" name="especie" required>
    </label><br>

    <label>Raza:
        <input type="text" name="raza" required>
    </label><br>

    <label>Fecha de nacimiento:
        <input type="date" name="fecha_nacimiento" required>
    </label><br>

    <label>Sexo:
        <select name="sexo" required>
            <option value="">-- Selecciona --</option>
            <option value="Macho">Macho</option>
            <option value="Hembra">Hembra</option>
        </select>
    </label><br>

    <label>Peso (kg):
        <input type="number" name="peso" step="0.01" min="0" required>
    </label><br>

    <button type="submit">Registrar</button>
</form>

<a href="/mascota">Volver a la lista</a>