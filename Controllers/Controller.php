<?php
abstract class Controller
{
    protected function iniciarSesion(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function vista(string $ruta, array $datos = []): void
    {
        $defaults = [
            'error' => null,
            'mensaje' => null,
            'roles' => [],
            'usuarios' => [],
            'mascotas' => [],
            'mascota' => null,
            'citasProximas' => [],
            'cita' => [],
            'veterinarios' => [],
            'totalMascotas' => 0,
            'totalUsuarios' => 0,
            'tituloPagina' => '',
            'titulo' => '',
            'nombre' => '',
            'rolId' => null,
            'usuario' => [],
            'sugerencias' => [],
            'termino' => '',
        ];

        extract($defaults);
        extract($datos);

        $vistaContenido = __DIR__ . '/../Views/' . $ruta . '.php';
        if (!file_exists($vistaContenido)) {
            die("Vista no encontrada: $ruta");
        }

        require $vistaContenido;
    }

    protected function redireccionar(string $url): void
    {
        header("Location: $url");
        exit;
    }

    protected function json(array $datos, int $codigo = 200): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json');
        echo json_encode($datos);
        exit;
    }

    protected function requiereSesion(array $rolesPermitidos = []): void
    {
        $this->iniciarSesion();

        if (!isset($_SESSION['usuario_id'])) {
            $this->redireccionar('/auth');
        }

        if (!empty($rolesPermitidos) && !in_array($_SESSION['rol_id'], $rolesPermitidos)) {
            http_response_code(403);
            die('Acceso no autorizado para tu rol.');
        }
    }
}