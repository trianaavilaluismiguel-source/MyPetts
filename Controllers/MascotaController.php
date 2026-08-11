<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Mascota.php';

class MascotaController extends Controller
{
    private Mascota $mascotaModel;

    public function __construct()
    {
        $this->mascotaModel = new Mascota();
    }

    // HU-03: lista de mascotas (propias si es DueñoMascota, todas si es staff)
    public function index(): void
    {
        $this->requiereSesion();

        // rol_id = 4 -> DueñoMascota (según tu catálogo de roles)
        if ($_SESSION['rol_id'] == 4) {
            $mascotas = $this->mascotaModel->buscarPorDueno($_SESSION['usuario_id']);
        } else {
            $mascotas = $this->mascotaModel->todosActivos();
        }

        // Mensaje de confirmación (flash message) dejado por crear/editar/eliminar
        $mensaje = $_SESSION['mensaje'] ?? null;
        unset($_SESSION['mensaje']);

        $this->vista('mascotas/index', ['mascotas' => $mascotas, 'mensaje' => $mensaje]);
    }

    // Muestra el formulario para registrar una mascota nueva
    public function mostrarCrear(): void
    {
        $this->requiereSesion();
        $this->vista('mascotas/crear');
    }

    // Procesa el formulario de registro de mascota
    public function crear(): void
    {
        $this->requiereSesion();

        $nombre           = trim($_POST['nombre'] ?? '');
        $especie          = trim($_POST['especie'] ?? '');
        $raza             = trim($_POST['raza'] ?? '');
        $fechaNacimiento  = $_POST['fecha_nacimiento'] ?? '';
        $sexo             = $_POST['sexo'] ?? '';
        $peso             = trim($_POST['peso'] ?? '');

        // HU-03 Esc.1/Esc.3: nombre, especie, raza, fecha de nacimiento, sexo y peso son obligatorios
        if ($nombre === '' || $especie === '' || $raza === '' || $fechaNacimiento === '' || $sexo === '' || $peso === '') {
            $this->vista('mascotas/crear', ['error' => 'Nombre, especie, raza, fecha de nacimiento, sexo y peso son obligatorios.']);
            return;
        }

        // Nota: no enviamos 'estado_salud' -> la columna es ENUM('al_dia','pendiente_atencion')
        // con DEFAULT 'al_dia', así que MySQL lo asigna automáticamente al crear.
        $this->mascotaModel->crear([
            'dueno_id'         => $_SESSION['usuario_id'],
            'nombre'           => $nombre,
            'especie'          => $especie,
            'raza'             => $raza,
            'fecha_nacimiento' => $fechaNacimiento,
            'sexo'             => $sexo,
            'peso'             => $peso,
            'activa'           => 1,
        ]);

        $_SESSION['mensaje'] = 'La mascota fue registrada correctamente.';
        $this->redireccionar('/mascota');
    }

    // Muestra el formulario para editar una mascota existente
    public function mostrarEditar(int $id): void
    {
        $this->requiereSesion();

        $mascota = $this->mascotaModel->buscarPorId($id);
        if (!$mascota) {
            $this->redireccionar('/mascota');
            return;
        }

        $this->vista('mascotas/editar', ['mascota' => $mascota]);
    }

    // Procesa el formulario de edición
    public function editar(int $id): void
    {
        $this->requiereSesion();

        $mascota = $this->mascotaModel->buscarPorId($id);
        if (!$mascota) {
            $this->redireccionar('/mascota');
            return;
        }

        $nombre          = trim($_POST['nombre'] ?? '');
        $especie         = trim($_POST['especie'] ?? '');
        $raza            = trim($_POST['raza'] ?? '');
        $fechaNacimiento = $_POST['fecha_nacimiento'] ?? '';
        $sexo            = $_POST['sexo'] ?? '';
        $peso            = trim($_POST['peso'] ?? '');
        $estadoSalud     = $_POST['estado_salud'] ?? '';

        if ($nombre === '' || $especie === '' || $raza === '' || $fechaNacimiento === '' || $sexo === '' || $peso === '') {
            $this->vista('mascotas/editar', ['mascota' => $mascota, 'error' => 'Nombre, especie, raza, fecha de nacimiento, sexo y peso son obligatorios.']);
            return;
        }

        // estado_salud es ENUM('al_dia','pendiente_atencion'); si viene vacío o inválido, se conserva el actual
        $valoresValidos = ['al_dia', 'pendiente_atencion'];
        $estadoSalud = in_array($estadoSalud, $valoresValidos) ? $estadoSalud : $mascota['estado_salud'];

        $this->mascotaModel->actualizar($id, [
            'nombre'           => $nombre,
            'especie'          => $especie,
            'raza'             => $raza,
            'fecha_nacimiento' => $fechaNacimiento,
            'sexo'             => $sexo,
            'peso'             => $peso,
            'estado_salud'     => $estadoSalud,
        ]);

        // HU-03 Esc.2: mensaje de confirmación exacto según el criterio de aceptación
        $_SESSION['mensaje'] = 'Los datos de la mascota han sido actualizados correctamente.';
        $this->redireccionar('/mascota');
    }

    // HU-03 Esc.7: baja lógica (no elimina el registro, solo lo marca inactivo)
    public function eliminar(int $id): void
    {
        $this->requiereSesion();
        $this->mascotaModel->darDeBaja($id);
        $_SESSION['mensaje'] = 'La mascota fue dada de baja correctamente.';
        $this->redireccionar('/mascota');
    }

    // HU-03 Esc.6: búsqueda/filtro por nombre, especie o número de registro
    public function buscar(): void
    {
        $this->requiereSesion();

        $termino = trim($_GET['q'] ?? '');
        $mascotas = $termino !== '' ? $this->mascotaModel->buscar($termino) : $this->mascotaModel->todosActivos();

        $this->vista('mascotas/index', ['mascotas' => $mascotas, 'termino' => $termino]);
    }
}