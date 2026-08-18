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

    public function index(): void
    {
        $this->vista('auth/login');
    }

    public function mostrarRegistro(): void
    {
        $this->vista('auth/registro');
    }

    public function registro(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->registrar();
        } else {
            $this->mostrarRegistro();
        }
    }

    // HU-01: Registro de usuario (Rol DueñoMascota = 4)
    public function registrar(): void
    {
        $nombre    = trim($_POST['nombre'] ?? '');
        $correo    = trim($_POST['correo'] ?? '');
        $clave     = $_POST['contrasena'] ?? '';
        $claveConf = $_POST['confirmar_contrasena'] ?? '';
        $telefono  = trim($_POST['telefono'] ?? '');

        // HU-01 Esc.3: Campos obligatorios
        if ($nombre === '' || $correo === '' || $clave === '' || $telefono === '') {
            $this->vista('auth/registro', ['error' => 'Todos los campos obligatorios deben ser diligenciados.']);
            return;
        }

        // HU-01 Esc.4: Contraseña débil (mínimo 8 caracteres, combinación alfanumérica)
        if (strlen($clave) < 8 || !preg_match('/[A-Za-z]/', $clave) || !preg_match('/[0-9]/', $clave)) {
            $this->vista('auth/registro', ['error' => 'La contraseña debe tener mínimo 8 caracteres, incluyendo letras y números.']);
            return;
        }

        // HU-01 Esc.5: Confirmación de contraseña no coincide
        if ($clave !== $claveConf) {
            $this->vista('auth/registro', ['error' => 'Las contraseñas no coinciden.']);
            return;
        }

        // HU-01 Esc.2: Correo ya registrado
        if ($this->usuarioModel->buscarPorCorreo($correo)) {
            $this->vista('auth/registro', ['error' => 'El correo ya está en uso. Inicie sesión o utilice otro correo.']);
            return;
        }

        // HU-01 Esc.1: Registro exitoso con rol_id = 4 (DueñoMascota)
        $rolDuenoMascota = 4;
        $this->usuarioModel->registrar($nombre, $correo, $clave, $telefono, $rolDuenoMascota);

        $this->redireccionar('/auth');
    }

    // HU-02: Inicio de sesión
    public function login(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $correo = trim($_POST['correo'] ?? '');
        $clave  = $_POST['contrasena'] ?? '';

        $usuario = $this->usuarioModel->buscarPorCorreo($correo);

        if (!$usuario) {
            $this->vista('auth/login', ['error' => 'Acceso denegado. El correo o la contraseña son incorrectos. Intente nuevamente.']);
            return;
        }

        // HU-02 Esc.4: Control de bloqueo temporal por 5 minutos
        if ($usuario['bloqueado_hasta'] && strtotime($usuario['bloqueado_hasta']) > time()) {
            $this->vista('auth/login', ['error' => 'Cuenta bloqueada temporalmente por exceso de intentos fallidos. Intente más tarde.']);
            return;
        }

        // HU-02 Esc.3: Cuenta desactivada
        if (!$usuario['activo']) {
            $this->vista('auth/login', ['error' => 'Tu cuenta está desactivada. Contacta al administrador de la clínica.']);
            return;
        }

        // HU-02 Esc.2: Verificación de contraseña e incremento de intentos fallidos
        if (!$this->usuarioModel->verificarContrasena($clave, $usuario['contrasena_hash'])) {
            $intentos = $usuario['intentos_fallidos'] + 1;
            $bloqueo  = null;

            if ($intentos >= 3) {
                $bloqueo = date('Y-m-d H:i:s', strtotime('+5 minutes'));
            }

            $this->usuarioModel->actualizar($usuario['id'], [
                'intentos_fallidos' => $intentos,
                'bloqueado_hasta'   => $bloqueo
            ]);

            $mensaje = ($intentos >= 3)
                ? 'Ha superado el límite de intentos. Cuenta bloqueada por 5 minutos.'
                : 'Acceso denegado. El correo o la contraseña son incorrectos. Intente nuevamente.';

            $this->vista('auth/login', ['error' => $mensaje]);
            return;
        }

        // HU-02 Esc.1: Inicio de sesión exitoso -> restablece intentos fallidos
        $this->usuarioModel->actualizar($usuario['id'], [
            'intentos_fallidos' => 0,
            'bloqueado_hasta'   => null
        ]);

        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre']     = $usuario['nombre'];
        $_SESSION['rol_id']     = $usuario['rol_id'];

        // HU-07: Cambio de contraseña obligatorio si fue creada por Administrador
        if ($usuario['requiere_cambio_pwd']) {
            $this->redireccionar('/auth/mostrarCambioPassword');
            return;
        }

        $this->redireccionar('/dashboard');
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        $this->redireccionar('/auth');
    }


    // Muestra la pantalla para cambiar la contraseña
    public function olvidePassword(): void
    {
        $this->vista('auth/cambiar-password');
    }

    
}