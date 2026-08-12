<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/HistorialClinico.php';
require_once __DIR__ . '/../Models/Mascota.php';

class HistorialController extends Controller
{
    private HistorialClinico $historialModel;
    private Mascota $mascotaModel;

    private const ROL_VETERINARIO = 2;

    public function __construct()
    {
        $this->historialModel = new HistorialClinico();
        $this->mascotaModel = new Mascota();
    }

    // HU-05 Esc.2/Esc.3: ver el historial clínico de una mascota
    public function verHistorial(int $mascotaId): void
    {
        $this->requiereSesion();

        $mascota = $this->mascotaModel->buscarPorId($mascotaId);
        if (!$mascota) {
            $this->redireccionar('/mascota');
            return;
        }

        $entradas = $this->historialModel->buscarPorMascota($mascotaId);

        $mensaje = $_SESSION['mensaje'] ?? null;
        unset($_SESSION['mensaje']);

        $this->vista('historial/index', ['mascota' => $mascota, 'entradas' => $entradas, 'mensaje' => $mensaje]);
    }

    // Muestra el formulario para registrar una nueva entrada clínica
    public function mostrarCrear(int $mascotaId): void
    {
        $this->requiereSesion([self::ROL_VETERINARIO]);

        $mascota = $this->mascotaModel->buscarPorId($mascotaId);
        if (!$mascota) {
            $this->redireccionar('/mascota');
            return;
        }

        $this->vista('historial/crear', ['mascota' => $mascota]);
    }

    // HU-05 Esc.1: procesa el formulario de nueva entrada clínica
    public function crear(int $mascotaId): void
    {
        $this->requiereSesion([self::ROL_VETERINARIO]);

        $mascota = $this->mascotaModel->buscarPorId($mascotaId);
        if (!$mascota) {
            $this->redireccionar('/mascota');
            return;
        }

        $motivoConsulta = trim($_POST['motivo_consulta'] ?? '');
        $diagnostico    = trim($_POST['diagnostico'] ?? '');
        $tratamiento    = trim($_POST['tratamiento'] ?? '');
        $observaciones  = trim($_POST['observaciones'] ?? '');

        if ($motivoConsulta === '' || $diagnostico === '' || $tratamiento === '') {
            $this->vista('historial/crear', [
                'mascota' => $mascota,
                'error' => 'Motivo de consulta, diagnóstico y tratamiento son obligatorios.',
            ]);
            return;
        }

        $this->historialModel->registrarEntrada([
            'mascota_id'      => $mascotaId,
            'veterinario_id'  => $_SESSION['usuario_id'],
            'motivo_consulta' => $motivoConsulta,
            'diagnostico'     => $diagnostico,
            'tratamiento'     => $tratamiento,
            'observaciones'   => $observaciones,
        ]);

        $_SESSION['mensaje'] = 'La entrada clínica fue registrada correctamente.';
        $this->redireccionar('/historial/verHistorial/' . $mascotaId);
    }

    // Muestra el formulario de edición, solo si sigue dentro de las 24h
    public function mostrarEditar(int $id): void
    {
        $this->requiereSesion([self::ROL_VETERINARIO]);

        $entrada = $this->historialModel->buscarPorId($id);
        if (!$entrada) {
            $this->redireccionar('/mascota');
            return;
        }

        // HU-05 Esc.6: fuera de las 24h, no se puede editar
        if (!$this->historialModel->puedeEditar($entrada)) {
            $_SESSION['mensaje'] = 'Esta entrada ya no se puede editar: pasaron más de 24 horas desde su registro.';
            $this->redireccionar('/historial/verHistorial/' . $entrada['mascota_id']);
            return;
        }

        $this->vista('historial/editar', ['entrada' => $entrada]);
    }

    // HU-05 Esc.6: procesa la edición
    public function editar(int $id): void
    {
        $this->requiereSesion([self::ROL_VETERINARIO]);

        $entrada = $this->historialModel->buscarPorId($id);
        if (!$entrada) {
            $this->redireccionar('/mascota');
            return;
        }

        // Se vuelve a validar aquí, por si el usuario dejó la pestaña abierta más de 24h
        if (!$this->historialModel->puedeEditar($entrada)) {
            $_SESSION['mensaje'] = 'Esta entrada ya no se puede editar: pasaron más de 24 horas desde su registro.';
            $this->redireccionar('/historial/verHistorial/' . $entrada['mascota_id']);
            return;
        }

        $motivoConsulta = trim($_POST['motivo_consulta'] ?? '');
        $diagnostico    = trim($_POST['diagnostico'] ?? '');
        $tratamiento    = trim($_POST['tratamiento'] ?? '');
        $observaciones  = trim($_POST['observaciones'] ?? '');

        if ($motivoConsulta === '' || $diagnostico === '' || $tratamiento === '') {
            $this->vista('historial/editar', [
                'entrada' => $entrada,
                'error' => 'Motivo de consulta, diagnóstico y tratamiento son obligatorios.',
            ]);
            return;
        }

        $this->historialModel->editarEntrada($id, [
            'motivo_consulta' => $motivoConsulta,
            'diagnostico'     => $diagnostico,
            'tratamiento'     => $tratamiento,
            'observaciones'   => $observaciones,
        ], $_SESSION['usuario_id']);

        $_SESSION['mensaje'] = 'La entrada clínica fue actualizada correctamente.';
        $this->redireccionar('/historial/verHistorial/' . $entrada['mascota_id']);
    }
}