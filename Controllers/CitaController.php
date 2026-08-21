<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Cita.php';
require_once __DIR__ . '/../Models/Mascota.php';
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Helpers/Mailer.php';

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

        // HU-04 Esc.1: confirmación por correo al dueño y al veterinario asignado
        $cuerpoConfirmacion = "<p>Se agendó una cita para el " . date('d/m/Y', strtotime($fecha)) . " a las " . date('h:i A', strtotime($hora)) . ".</p><p>Tipo de consulta: " . htmlspecialchars($tipoConsulta) . "</p>";
        $this->notificarDueno((int) $mascotaId, 'Cita agendada - MyPetts', $cuerpoConfirmacion);
        $this->notificarVeterinario((int) $veterinarioId, 'Nueva cita asignada - MyPetts', $cuerpoConfirmacion);

        $_SESSION['mensaje'] = 'La cita fue agendada correctamente.';
        $this->redireccionar('/cita');
    }

    // Notifica por correo al dueño de la mascota asociada a una cita
    private function notificarDueno(int $mascotaId, string $asunto, string $cuerpoHtml): void
    {
        $mascota = $this->mascotaModel->buscarPorId($mascotaId);
        if (!$mascota) {
            return;
        }
        $dueno = $this->usuarioModel->buscarPorId((int) $mascota['dueno_id']);
        if (!$dueno) {
            return;
        }
        Mailer::enviar($dueno['correo'], $asunto, $cuerpoHtml);
    }

    // Notifica por correo al veterinario asignado a una cita
    private function notificarVeterinario(int $veterinarioId, string $asunto, string $cuerpoHtml): void
    {
        $veterinario = $this->usuarioModel->buscarPorId($veterinarioId);
        if ($veterinario) {
            Mailer::enviar($veterinario['correo'], $asunto, $cuerpoHtml);
        }
    }

    // Notifica por correo a todo el personal de recepción
    private function notificarRecepcion(string $asunto, string $cuerpoHtml): void
    {
        foreach ($this->usuarioModel->buscarPorRol(3) as $recepcionista) {
            Mailer::enviar($recepcionista['correo'], $asunto, $cuerpoHtml);
        }
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

        $this->verificarPropiedad($cita);

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

        $cuerpoCancelacion = "<p>La cita del " . date('d/m/Y', strtotime($cita['fecha'])) . " a las " . date('h:i A', strtotime($cita['hora'])) . " fue cancelada.</p>";
        $this->notificarDueno((int) $cita['mascota_id'], 'Cita cancelada - MyPetts', $cuerpoCancelacion);
        $this->notificarVeterinario((int) $cita['veterinario_id'], 'Cita cancelada - MyPetts', $cuerpoCancelacion);
        $this->notificarRecepcion('Cita cancelada - MyPetts', $cuerpoCancelacion);

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

        $this->verificarPropiedad($cita);

        $this->vista('citas/reagendar', ['cita' => $cita]);
    }

    // HU-04 Esc.7: procesa el reagendamiento (crea una cita nueva, no sobrescribe la original)
    public function reagendar(int $id): void
    {
        $this->requiereSesion();

        $cita = $this->citaModel->buscarPorId($id);
        if (!$cita) {
            $this->redireccionar('/cita');
            return;
        }

        $this->verificarPropiedad($cita);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->vista('citas/reagendar', ['cita' => $cita]);
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

        $this->citaModel->reagendar($cita, $nuevaFecha, $nuevaHora);

        $cuerpoReagendado = "<p>La cita fue reagendada para el " . date('d/m/Y', strtotime($nuevaFecha)) . " a las " . date('h:i A', strtotime($nuevaHora)) . ".</p>";
        $this->notificarDueno((int) $cita['mascota_id'], 'Cita reagendada - MyPetts', $cuerpoReagendado);
        $this->notificarVeterinario((int) $cita['veterinario_id'], 'Cita reagendada - MyPetts', $cuerpoReagendado);
        $this->notificarRecepcion('Cita reagendada - MyPetts', $cuerpoReagendado);

        $_SESSION['mensaje'] = 'La cita fue reagendada correctamente.';
        $this->redireccionar('/cita');
    }

    // Un Dueño (rol 4) solo puede cancelar/reagendar citas de sus propias mascotas.
    // El resto de roles (Admin/Veterinario/Recepcionista) tiene acceso completo.
    private function verificarPropiedad(array $cita): void
    {
        if ((int) $_SESSION['rol_id'] !== self::ROL_DUENO) {
            return;
        }

        $mascota = $this->mascotaModel->buscarPorId((int) $cita['mascota_id']);
        if (!$mascota || (int) $mascota['dueno_id'] !== (int) $_SESSION['usuario_id']) {
            http_response_code(403);
            die('No tienes permiso para modificar esta cita.');
        }
    }
}