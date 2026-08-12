<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Usuario.php';

class AuthController extends Controller
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    // Muestra el formulario de login
    public function index(): void
    {
        $this->vista('auth/login');
    }

    // Muestra el formulario de registro
    public function mostrarRegistro(): void
    {
        $this->vista('auth/registro');
    }

    // HU-01: procesa el formulario de registro
    public function registrar(): void
    {
        $nombre   = trim($_POST['nombre'] ?? '');
        $correo   = trim($_POST['correo'] ?? '');
        $clave    = $_POST['contrasena'] ?? '';
        $claveConf = $_POST['confirmar_contrasena'] ?? '';
        $telefono = trim($_POST['telefono'] ?? '');

        // Esc.3: campos obligatorios
        if ($nombre === '' || $correo === '' || $clave === '' || $telefono === '') {
            $this->vista('auth/registro', ['error' => 'Todos los campos son obligatorios.']);
            return;
        }

        // Esc.4: contraseña débil (mínimo 8 caracteres, letras y números)
        if (strlen($clave) < 8 || !preg_match('/[A-Za-z]/', $clave) || !preg_match('/[0-9]/', $clave)) {
            $this->vista('auth/registro', ['error' => 'La contraseña debe tener mínimo 8 caracteres, con letras y números.']);
            return;
        }

        // Esc.5: confirmación no coincide
        if ($clave !== $claveConf) {
            $this->vista('auth/registro', ['error' => 'Las contraseñas no coinciden.']);
            return;
        }

        // Esc.2: correo ya registrado
        if ($this->usuarioModel->buscarPorCorreo($correo)) {
            $this->vista('auth/registro', ['error' => 'El correo ya está en uso. Inicie sesión o utilice otro correo.']);
            return;
        }

        // Registro exitoso: todo usuario que se registra solo, es DueñoMascota (rol_id = 4)
        $rolDuenoMascota = 4;
        $this->usuarioModel->registrar($nombre, $correo, $clave, $telefono, $rolDuenoMascota);

        $this->redireccionar('/auth');
    }

    // HU-02: procesa el formulario de login
    public function login(): void
    {
        session_start();

        $correo = trim($_POST['correo'] ?? '');
        $clave  = $_POST['contrasena'] ?? '';

        $usuario = $this->usuarioModel->buscarPorCorreo($correo);

        // Esc.2: credenciales incorrectas
        if (!$usuario || !$this->usuarioModel->verificarContrasena($clave, $usuario['contrasena_hash'])) {
            $this->vista('auth/login', ['error' => 'Acceso denegado. El correo o la contraseña son incorrectos. Intente nuevamente.']);
            return;
        }

        // Esc.3: cuenta desactivada
        if (!$usuario['activo']) {
            $this->vista('auth/login', ['error' => 'Tu cuenta está desactivada. Contacta al administrador de la clínica.']);
            return;
        }

        // Esc.4: cuenta bloqueada temporalmente
        if ($usuario['bloqueado_hasta'] && strtotime($usuario['bloqueado_hasta']) > time()) {
            $this->vista('auth/login', ['error' => 'Cuenta bloqueada temporalmente. Intenta de nuevo en unos minutos.']);
            return;
        }

        // Login correcto: reinicia intentos fallidos y guarda la sesión
        $this->usuarioModel->actualizar($usuario['id'], ['intentos_fallidos' => 0, 'bloqueado_hasta' => null]);

        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre']     = $usuario['nombre'];
        $_SESSION['rol_id']     = $usuario['rol_id'];

        // HU-07: si la cuenta tiene contraseña temporal, obliga a cambiarla antes de continuar
        if ($usuario['requiere_cambio_pwd']) {
            $this->redireccionar('/auth/mostrarCambioPassword');
            return;
        }

        $this->redireccionar('/dashboard');
    }

    // Muestra el formulario para cambiar la contraseña temporal
    public function mostrarCambioPassword(): void
    {
        $this->requiereSesion();
        $this->vista('auth/cambiar-password');
    }

    // Procesa el cambio de contraseña obligatorio
    public function cambiarPassword(): void
    {
        $this->requiereSesion();

        $claveNueva = $_POST['contrasena'] ?? '';
        $claveConf  = $_POST['confirmar_contrasena'] ?? '';

        if (strlen($claveNueva) < 8 || !preg_match('/[A-Za-z]/', $claveNueva) || !preg_match('/[0-9]/', $claveNueva)) {
            $this->vista('auth/cambiar-password', ['error' => 'La contraseña debe tener mínimo 8 caracteres, con letras y números.']);
            return;
        }

        if ($claveNueva !== $claveConf) {
            $this->vista('auth/cambiar-password', ['error' => 'Las contraseñas no coinciden.']);
            return;
        }

        $this->usuarioModel->cambiarContrasena($_SESSION['usuario_id'], $claveNueva);

        $this->redireccionar('/dashboard');
    }

    public function logout(): void
    {
        session_start();
        session_destroy();
        $this->redireccionar('/auth');
    }
}
