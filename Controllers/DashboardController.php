<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Cita.php';
require_once __DIR__ . '/../Models/Usuario.php';

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requiereSesion();

        $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
        $nombre = $_SESSION['nombre'] ?? 'Usuario';
        $rolId = $_SESSION['rol_id'] ?? null;

        $usuario = [
            'id' => $usuarioId,
            'nombre' => $nombre,
            'rol_id' => $rolId,
        ];

        $mascotas = [];
        $citasProximas = [];
        $totalMascotas = 0;
        $totalUsuarios = 0;

        $citaModel = new Cita();
        $mascotaModel = new Mascota();
        $usuarioModel = new Usuario();

        if ((int) $rolId === 4) { // DueñoMascota
            $mascotas = $mascotaModel->buscarPorDueno($usuarioId);
            $citasProximas = $citaModel->proximasPorDueno($usuarioId);
        } elseif ((int) $rolId === 2) { // Veterinario
            $citasProximas = $citaModel->proximasPorVeterinario($usuarioId);
        } else { // Recepcionista o Administrador
            $totalMascotas = count($mascotaModel->todosActivos());
            $totalUsuarios = count($usuarioModel->listarConRol());
            $citasProximas = $citaModel->proximasTodas();
        }

        $this->vista('dashboard/index', [
            'usuario' => $usuario,
            'nombre' => $nombre,
            'rolId' => $rolId,
            'mascotas' => $mascotas,
            'citas' => $citasProximas,
            'totalMascotas' => $totalMascotas,
            'totalUsuarios' => $totalUsuarios,
        ]);
    }
}