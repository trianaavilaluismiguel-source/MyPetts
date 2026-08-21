<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/VacunaDesparasitacion.php';
require_once __DIR__ . '/../Models/Mascota.php';

class VacunaController extends Controller
{
    private VacunaDesparasitacion $vacunaModel;
    private Mascota $mascotaModel;

    private const ROL_VETERINARIO = 2;
    private const ROL_DUENO = 4;

    public function __construct()
    {
        $this->vacunaModel = new VacunaDesparasitacion();
        $this->mascotaModel = new Mascota();
    }

    // HU-05 Esc.4/Esc.5: lista de vacunas/desparasitaciones de una mascota, con alertas
    public function verVacunas(int $mascotaId): void
    {
        $this->requiereSesion();

        $mascota = $this->mascotaModel->buscarPorId($mascotaId);
        if (!$mascota) {
            $this->redireccionar('/mascota');
            return;
        }

        $this->verificarPropiedad($mascota);

        $registros = $this->vacunaModel->buscarPorMascota($mascotaId);

        $mensaje = $_SESSION['mensaje'] ?? null;
        unset($_SESSION['mensaje']);

        $this->vista('vacunas/index', ['mascota' => $mascota, 'registros' => $registros, 'mensaje' => $mensaje]);
    }

    // Muestra el formulario para registrar una vacuna o desparasitación
    public function mostrarCrear(int $mascotaId): void
    {
        $this->requiereSesion([self::ROL_VETERINARIO]);

        $mascota = $this->mascotaModel->buscarPorId($mascotaId);
        if (!$mascota) {
            $this->redireccionar('/mascota');
            return;
        }

        $this->vista('vacunas/crear', ['mascota' => $mascota]);
    }

    // HU-05 Esc.4: procesa el formulario
    public function crear(int $mascotaId): void
    {
        $this->requiereSesion([self::ROL_VETERINARIO]);

        $mascota = $this->mascotaModel->buscarPorId($mascotaId);
        if (!$mascota) {
            $this->redireccionar('/mascota');
            return;
        }

        $tipo               = $_POST['tipo'] ?? '';
        $nombreProducto     = trim($_POST['nombre_producto'] ?? '');
        $lote               = trim($_POST['lote'] ?? '');
        $fechaAplicacion    = $_POST['fecha_aplicacion'] ?? '';
        $fechaProximaDosis  = $_POST['fecha_proxima_dosis'] ?? '';

        if ($tipo === '' || $nombreProducto === '' || $lote === '' || $fechaAplicacion === '' || $fechaProximaDosis === '') {
            $this->vista('vacunas/crear', [
                'mascota' => $mascota,
                'error' => 'Todos los campos son obligatorios.',
            ]);
            return;
        }

        $this->vacunaModel->registrar([
            'mascota_id'          => $mascotaId,
            'veterinario_id'      => $_SESSION['usuario_id'],
            'tipo'                => $tipo,
            'nombre_producto'     => $nombreProducto,
            'lote'                => $lote,
            'fecha_aplicacion'    => $fechaAplicacion,
            'fecha_proxima_dosis' => $fechaProximaDosis,
        ]);

        $_SESSION['mensaje'] = 'El registro fue guardado correctamente.';
        $this->redireccionar('/vacuna/verVacunas/' . $mascotaId);
    }

    // Un Dueño (rol 4) solo puede ver las vacunas de sus propias mascotas.
    // El resto de roles (Admin/Veterinario/Recepcionista) tiene acceso completo.
    private function verificarPropiedad(array $mascota): void
    {
        if ((int) $_SESSION['rol_id'] === self::ROL_DUENO && (int) $mascota['dueno_id'] !== (int) $_SESSION['usuario_id']) {
            http_response_code(403);
            die('No tienes permiso para ver las vacunas de esta mascota.');
        }
    }
}