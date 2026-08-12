<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Cita.php';
require_once __DIR__ . '/../Models/Usuario.php';

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requiereSesion();

        $nombre = $_SESSION['nombre'] ?? null;
        $rolId = $_SESSION['rol_id'] ?? null;

        // Preparar datos según rol
        $mascotas = [];
        $citasProximas = [];
        $totalMascotas = 0;
        $totalUsuarios = 0;

        require_once __DIR__ . '/../Models/Cita.php';
        require_once __DIR__ . '/../Models/Mascota.php';
        require_once __DIR__ . '/../Models/Usuario.php';

        $citaModel = new Cita();
        $mascotaModel = new Mascota();
        $usuarioModel = new Usuario();

        if ((int) $rolId === 4) { // DueñoMascota
            $mascotas = $mascotaModel->buscarPorDueno((int) $_SESSION['usuario_id']);
            $citasProximas = $citaModel->proximasPorDueno((int) $_SESSION['usuario_id']);
        } elseif ((int) $rolId === 2) { // Veterinario
            $citasProximas = $citaModel->proximasPorVeterinario((int) $_SESSION['usuario_id']);
        } else { // Recepcionista o Administrador
            $totalMascotas = count($mascotaModel->todosActivos());
            $totalUsuarios = count($usuarioModel->listarConRol());
            $citasProximas = $citaModel->proximasTodas();
        }

        $this->vista('dashboard/index', [
            'nombre' => $nombre,
            'rolId' => $rolId,
            'mascotas' => $mascotas,
            'citasProximas' => $citasProximas,
            'totalMascotas' => $totalMascotas,
            'totalUsuarios' => $totalUsuarios,
        ]);
    }
}