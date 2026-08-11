<?php
abstract class Controller
{
    protected function vista(string $ruta, array $datos = []): void
    {
        extract($datos);
        $archivo = __DIR__ . '/../Views/' . $ruta . '.php';
        if (!file_exists($archivo)) {
            die("Vista no encontrada: $ruta");
        }
        require $archivo;
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

    // HU-02: verifica sesión activa y, opcionalmente, rol permitido
    protected function requiereSesion(array $rolesPermitidos = []): void
    {
        session_start();
        if (!isset($_SESSION['usuario_id'])) {
            $this->redireccionar('/auth');
        }
        if (!empty($rolesPermitidos) && !in_array($_SESSION['rol_id'], $rolesPermitidos)) {
            http_response_code(403);
            die('Acceso no autorizado para tu rol.');
        }
    }
}