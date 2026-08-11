<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Cita.php';
require_once __DIR__ . '/../Models/Mascota.php';
require_once __DIR__ . '/../Models/Usuario.php';

class CitaController extends Controller
{
    private Cita $citaModel;
    private Mascota $mascotaModel;
    private Usuario $usuarioModel;

    // rol_id según tu tabla roles
    private const ROL_VETERINARIO = 2;
    private const ROL_DUENO = 4;

    public function __construct()
    {
        $this->citaModel = new Cita();
        $this->mascotaModel = new Mascota();
        $this->usuarioModel = new Usuario();
    }

    // HU-04 Esc.3/Esc.4: lista de citas, según el rol del usuario
    public function index(): void
    {
        $this->requiereSesion();

        $citas = match ($_SESSION['rol_id']) {
            self::ROL_DUENO       => $this->citaModel->buscarPorDueno($_SESSION['usuario_id']),
            self::ROL_VETERINARIO => $this->citaModel->buscarPorVeterinario($_SESSION['usuario_id']),
            default                => $this->citaModel->todas(), // Administrador / Recepcionista
        };

        $mensaje = $_SESSION['mensaje'] ?? null;
        unset($_SESSION['mensaje']);

        $this->vista('citas/index', ['citas' => $citas, 'mensaje' => $mensaje]);
    }

    // Muestra el formulario para agendar una cita nueva
    public function mostrarCrear(): void
    {
        $this->requiereSesion();

        // Solo mascotas del dueño logueado (si es DueñoMascota); si es staff, todas las activas
        $mascotas = $_SESSION['rol_id'] == self::ROL_DUENO
            ? $this->mascotaModel->buscarPorDueno($_SESSION['usuario_id'])
            : $this->mascotaModel->todosActivos();

        $veterinarios = $this->usuarioModel->buscarPorRol(self::ROL_VETERINARIO);

        $this->vista('citas/crear', ['mascotas' => $mascotas, 'veterinarios' => $veterinarios]);
    }

    // HU-04 Esc.1/Esc.2: procesa el formulario de agendamiento
    public function crear(): void
    {
        $this->requiereSesion();

        $mascotaId      = $_POST['mascota_id'] ?? '';
        $veterinarioId  = $_POST['veterinario_id'] ?? '';
        $fecha          = $_POST['fecha'] ?? '';
        $hora           = $_POST['hora'] ?? '';
        $tipoConsulta   = trim($_POST['tipo_consulta'] ?? '');

        if ($mascotaId === '' || $veterinarioId === '' || $fecha === '' || $hora === '' || $tipoConsulta === '') {
            $this->recargarFormularioConError('Todos los campos son obligatorios.');
            return;
        }

        // HU-04 Esc.2: horario no disponible -> se sugieren los próximos 3 libres
        if (!$this->citaModel->horarioDisponible((int) $veterinarioId, $fecha, $hora)) {
            $sugerencias = $this->citaModel->sugerirHorariosLibres((int) $veterinarioId, $fecha);
            $this->recargarFormularioConError('Horario no disponible.', $sugerencias);
            return;
        }

        $this->citaModel->crear([
            'mascota_id'     => $mascotaId,
            'veterinario_id' => $veterinarioId,
            'agendada_por'   => $_SESSION['usuario_id'],
            'fecha'          => $fecha,
            'hora'           => $hora,
            'tipo_consulta'  => $tipoConsulta,
            'estado'         => 'agendada',
        ]);

        $_SESSION['mensaje'] = 'La cita fue agendada correctamente.';
        $this->redireccionar('/cita');
    }

    // Vuelve a mostrar el formulario de creación con el error (y sugerencias, si aplica)
    private function recargarFormularioConError(string $error, array $sugerencias = []): void
    {
        $mascotas = $_SESSION['rol_id'] == self::ROL_DUENO
            ? $this->mascotaModel->buscarPorDueno($_SESSION['usuario_id'])
            : $this->mascotaModel->todosActivos();

        $veterinarios = $this->usuarioModel->buscarPorRol(self::ROL_VETERINARIO);

        $this->vista('citas/crear', [
            'mascotas' => $mascotas,
            'veterinarios' => $veterinarios,
            'error' => $error,
            'sugerencias' => $sugerencias,
        ]);
    }

    // HU-04 Esc.5/Esc.6: cancela una cita (solo si faltan más de 24h)
    public function cancelar(int $id): void
    {
        $this->requiereSesion();

        $cita = $this->citaModel->buscarPorId($id);
        if (!$cita) {
            $this->redireccionar('/cita');
            return;
        }

        $fechaHoraCita = new DateTime($cita['fecha'] . ' ' . $cita['hora']);
        $ahora = new DateTime();
        $horasRestantes = ($fechaHoraCita->getTimestamp() - $ahora->getTimestamp()) / 3600;

        // HU-04 Esc.6: cancelación fuera de plazo
        if ($horasRestantes < 24) {
            $_SESSION['mensaje'] = 'Cancelación tardía. Por favor, contacte directamente a la clínica para gestionar el cambio.';
            $this->redireccionar('/cita');
            return;
        }

        // HU-04 Esc.5: cancelación exitosa
        $this->citaModel->cancelar($id, 'Cancelada por el usuario');
        $_SESSION['mensaje'] = 'La cita fue cancelada correctamente.';
        $this->redireccionar('/cita');
    }

    // Muestra el formulario para reagendar una cita existente
    public function mostrarReagendar(int $id): void
    {
        $this->requiereSesion();

        $cita = $this->citaModel->buscarPorId($id);
        if (!$cita) {
            $this->redireccionar('/cita');
            return;
        }

        $this->vista('citas/reagendar', ['cita' => $cita]);
    }

    // HU-04 Esc.7: procesa el reagendamiento
    public function reagendar(int $id): void
    {
        $this->requiereSesion();

        $cita = $this->citaModel->buscarPorId($id);
        if (!$cita) {
            $this->redireccionar('/cita');
            return;
        }

        $nuevaFecha = $_POST['fecha'] ?? '';
        $nuevaHora  = $_POST['hora'] ?? '';

        if ($nuevaFecha === '' || $nuevaHora === '') {
            $this->vista('citas/reagendar', ['cita' => $cita, 'error' => 'Debes indicar la nueva fecha y hora.']);
            return;
        }

        if (!$this->citaModel->horarioDisponible((int) $cita['veterinario_id'], $nuevaFecha, $nuevaHora)) {
            $sugerencias = $this->citaModel->sugerirHorariosLibres((int) $cita['veterinario_id'], $nuevaFecha);
            $this->vista('citas/reagendar', ['cita' => $cita, 'error' => 'Horario no disponible.', 'sugerencias' => $sugerencias]);
            return;
        }

        $this->citaModel->reagendar($id, $nuevaFecha, $nuevaHora);
        $_SESSION['mensaje'] = 'La cita fue reagendada correctamente.';
        $this->redireccionar('/cita');
    }
}