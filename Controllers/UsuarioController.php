<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Helpers/Mailer.php';

class UsuarioController extends Controller
{
    private Usuario $usuarioModel;

    private const ROL_ADMINISTRADOR = 1;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    // HU-07: lista todos los usuarios del sistema
    public function index(): void
    {
        $this->requiereSesion([self::ROL_ADMINISTRADOR]);

        $usuarios = $this->usuarioModel->listarConRol();

        $mensaje = $_SESSION['mensaje'] ?? null;
        unset($_SESSION['mensaje']);

        $this->vista('usuarios/index', ['usuarios' => $usuarios, 'mensaje' => $mensaje]);
    }

    // Muestra el formulario para crear una cuenta nueva
    public function mostrarCrear(): void
    {
        $this->requiereSesion([self::ROL_ADMINISTRADOR]);

        $roles = $this->usuarioModel->obtenerRoles();

        $this->vista('usuarios/crear', ['roles' => $roles]);
    }

    // HU-07: procesa la creación de una cuenta por parte del Administrador
    public function crear(): void
    {
        $this->requiereSesion([self::ROL_ADMINISTRADOR]);

        $nombre   = trim($_POST['nombre'] ?? '');
        $correo   = trim($_POST['correo'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $rolId    = $_POST['rol_id'] ?? '';

        if ($nombre === '' || $correo === '' || $telefono === '' || $rolId === '') {
            $this->vista('usuarios/crear', [
                'roles' => $this->usuarioModel->obtenerRoles(),
                'error' => 'Todos los campos son obligatorios.',
            ]);
            return;
        }

        if ($this->usuarioModel->buscarPorCorreo($correo)) {
            $this->vista('usuarios/crear', [
                'roles' => $this->usuarioModel->obtenerRoles(),
                'error' => 'Ese correo ya está registrado.',
            ]);
            return;
        }

        $resultado = $this->usuarioModel->crearPorAdmin($nombre, $correo, $telefono, (int) $rolId, $_SESSION['usuario_id']);

        // HU-07: envía las credenciales temporales al correo del nuevo usuario
        Mailer::enviar(
            $correo,
            'Tu cuenta en MyPetts fue creada',
            "<p>Hola <strong>" . htmlspecialchars($nombre) . "</strong>,</p>"
            . "<p>Tu cuenta en MyPetts fue creada. Estas son tus credenciales de acceso:</p>"
            . "<p>Correo: {$correo}<br>Contraseña temporal: <strong>{$resultado['contrasena_temporal']}</strong></p>"
            . "<p>Se te pedirá cambiarla al iniciar sesión por primera vez.</p>"
        );

        $_SESSION['mensaje'] = 'Usuario creado correctamente. Se envió la contraseña temporal a su correo ('
            . $resultado['contrasena_temporal']
            . ' — visible aquí solo por si el correo no llega).';

        $this->redireccionar('/usuario');
    }

    // Muestra el formulario de edición de una cuenta
    public function mostrarEditar(int $id): void
    {
        $this->requiereSesion([self::ROL_ADMINISTRADOR]);

        $usuario = $this->usuarioModel->buscarPorId($id);
        if (!$usuario) {
            $this->redireccionar('/usuario');
            return;
        }

        $roles = $this->usuarioModel->obtenerRoles();

        $this->vista('usuarios/editar', ['usuario' => $usuario, 'roles' => $roles]);
    }

    // HU-07: procesa la edición de una cuenta
    public function editar(int $id): void
    {
        $this->requiereSesion([self::ROL_ADMINISTRADOR]);

        $usuario = $this->usuarioModel->buscarPorId($id);
        if (!$usuario) {
            $this->redireccionar('/usuario');
            return;
        }

        $nombre   = trim($_POST['nombre'] ?? '');
        $correo   = trim($_POST['correo'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $rolId    = $_POST['rol_id'] ?? '';

        if ($nombre === '' || $correo === '' || $telefono === '' || $rolId === '') {
            $this->vista('usuarios/editar', [
                'usuario' => $usuario,
                'roles' => $this->usuarioModel->obtenerRoles(),
                'error' => 'Todos los campos son obligatorios.',
            ]);
            return;
        }

        $this->usuarioModel->actualizarDatos($id, $nombre, $correo, $telefono, (int) $rolId);

        $_SESSION['mensaje'] = 'Los datos del usuario fueron actualizados.';
        $this->redireccionar('/usuario');
    }

    // HU-07: activa o desactiva una cuenta
    public function cambiarEstado(int $id): void
    {
        $this->requiereSesion([self::ROL_ADMINISTRADOR]);

        // Un administrador no puede desactivar su propia cuenta (evita quedarse fuera del sistema)
        if ($id === (int) $_SESSION['usuario_id']) {
            $_SESSION['mensaje'] = 'No puedes desactivar tu propia cuenta.';
            $this->redireccionar('/usuario');
            return;
        }

        $usuario = $this->usuarioModel->buscarPorId($id);
        if (!$usuario) {
            $this->redireccionar('/usuario');
            return;
        }

        $nuevoEstado = !$usuario['activo'];
        $this->usuarioModel->cambiarEstado($id, $nuevoEstado);

        // HU-07 Esc.4/Esc.5: se informa al usuario afectado por correo
        Mailer::enviar(
            $usuario['correo'],
            $nuevoEstado ? 'Tu cuenta en MyPetts fue reactivada' : 'Tu cuenta en MyPetts fue desactivada',
            $nuevoEstado
                ? "<p>Hola <strong>" . htmlspecialchars($usuario['nombre']) . "</strong>, tu cuenta fue reactivada y ya puedes iniciar sesión con tus datos de acceso.</p>"
                : "<p>Hola <strong>" . htmlspecialchars($usuario['nombre']) . "</strong>, tu cuenta fue desactivada temporalmente. Contacta al administrador de la clínica si tienes dudas.</p>"
        );

        $_SESSION['mensaje'] = $nuevoEstado ? 'Usuario activado.' : 'Usuario desactivado.';
        $this->redireccionar('/usuario');
    }

    // HU-07: resetea la contraseña de una cuenta a una temporal
    public function resetearContrasena(int $id): void
    {
        $this->requiereSesion([self::ROL_ADMINISTRADOR]);

        $usuario = $this->usuarioModel->buscarPorId($id);
        if (!$usuario) {
            $this->redireccionar('/usuario');
            return;
        }

        $contrasenaTemporal = $this->usuarioModel->resetearContrasena($id);

        Mailer::enviar(
            $usuario['correo'],
            'Tu contraseña en MyPetts fue restablecida',
            "<p>Hola <strong>" . htmlspecialchars($usuario['nombre']) . "</strong>,</p>"
            . "<p>Tu contraseña temporal es: <strong>{$contrasenaTemporal}</strong></p>"
            . "<p>Se te pedirá cambiarla al iniciar sesión.</p>"
        );

        $_SESSION['mensaje'] = 'Contraseña restablecida. Se envió la nueva contraseña temporal a su correo ('
            . $contrasenaTemporal
            . ' — visible aquí solo por si el correo no llega).';

        $this->redireccionar('/usuario');
    }
}