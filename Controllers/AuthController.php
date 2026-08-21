<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Helpers/Mailer.php';

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
        if (!$this->contrasenaEsValida($clave)) {
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

    private function contrasenaEsValida(string $clave): bool
    {
        return strlen($clave) >= 8
            && preg_match('/[A-Za-z]/', $clave)
            && preg_match('/[0-9]/', $clave);
    }

    // HU-02: Inicio de sesión
    public function login(): void
    {
        $this->iniciarSesion();

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

            // HU-02 Esc.4: notificar a los administradores cuando se bloquea una cuenta
            if ($intentos >= 3) {
                foreach ($this->usuarioModel->buscarPorRol(1) as $admin) {
                    Mailer::enviar(
                        $admin['correo'],
                        'Cuenta bloqueada por intentos fallidos - MyPetts',
                        "<p>La cuenta de <strong>" . htmlspecialchars($usuario['nombre']) . "</strong> ({$usuario['correo']}) fue bloqueada temporalmente por 5 minutos tras 3 intentos fallidos de inicio de sesión.</p>"
                    );
                }
            }

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
        $this->iniciarSesion();
        session_destroy();
        $this->redireccionar('/auth');
    }

    public function mostrarCambioPassword(): void
    {
        $this->iniciarSesion();

        if (!isset($_SESSION['usuario_id'])) {
            $this->redireccionar('/auth');
            return;
        }

        $this->vista('auth/cambiar-password');
    }

    public function cambiarPassword(): void
    {
        $this->iniciarSesion();

        if (!isset($_SESSION['usuario_id'])) {
            $this->redireccionar('/auth');
            return;
        }

        $clave     = $_POST['contrasena'] ?? '';
        $claveConf = $_POST['confirmar_contrasena'] ?? '';

        if ($clave === '' || $claveConf === '') {
            $this->vista('auth/cambiar-password', ['error' => 'Debes ingresar y confirmar la nueva contraseña.']);
            return;
        }

        if (!$this->contrasenaEsValida($clave)) {
            $this->vista('auth/cambiar-password', ['error' => 'La contraseña debe tener mínimo 8 caracteres, incluyendo letras y números.']);
            return;
        }

        if ($clave !== $claveConf) {
            $this->vista('auth/cambiar-password', ['error' => 'Las contraseñas no coinciden.']);
            return;
        }

        $usuario = $this->usuarioModel->buscarPorId((int) $_SESSION['usuario_id']);
        if (!$usuario) {
            $this->redireccionar('/auth');
            return;
        }

        $this->usuarioModel->cambiarContrasena((int) $_SESSION['usuario_id'], $clave);
        $_SESSION['mensaje'] = 'Contraseña actualizada correctamente.';
        $this->redireccionar('/dashboard');
    }

    // HU-02 Esc.5: muestra el formulario para pedir el correo de recuperación
    public function olvidePassword(): void
    {
        $this->vista('auth/olvide-password');
    }

    // HU-02 Esc.5: procesa la solicitud, genera el token y envía el correo
    public function enviarRecuperacion(): void
    {
        $correo = trim($_POST['correo'] ?? '');

        // Por seguridad, siempre mostramos el mismo mensaje exista o no el correo
        // (así no revelamos qué correos están registrados en el sistema)
        $mensaje = 'Si el correo está registrado, se envió un enlace de recuperación válido por 30 minutos.';

        $usuario = $correo !== '' ? $this->usuarioModel->buscarPorCorreo($correo) : false;

        if ($usuario) {
            $token = $this->usuarioModel->generarTokenRecuperacion((int) $usuario['id']);
            $enlace = 'http://' . $_SERVER['HTTP_HOST'] . '/auth/restablecer/' . $token;

            Mailer::enviar(
                $usuario['correo'],
                'Recupera tu contraseña - MyPetts',
                "<p>Hola <strong>" . htmlspecialchars($usuario['nombre']) . "</strong>,</p>"
                . "<p>Recibimos una solicitud para restablecer tu contraseña. Este enlace es válido por 30 minutos:</p>"
                . "<p><a href=\"{$enlace}\">{$enlace}</a></p>"
                . "<p>Si no solicitaste esto, ignora este correo.</p>"
            );
        }

        $this->vista('auth/olvide-password', ['mensaje' => $mensaje]);
    }

    // HU-02 Esc.5: muestra el formulario de nueva contraseña si el token es válido
    public function restablecer(string $token): void
    {
        $usuario = $this->usuarioModel->buscarPorTokenValido($token);

        if (!$usuario) {
            $this->vista('auth/restablecer-password', ['token' => null, 'error' => 'El enlace no es válido o ya expiró. Solicita uno nuevo.']);
            return;
        }

        $this->vista('auth/restablecer-password', ['token' => $token]);
    }

    // HU-02 Esc.5: procesa la nueva contraseña
    public function procesarRestablecer(string $token): void
    {
        $usuario = $this->usuarioModel->buscarPorTokenValido($token);

        if (!$usuario) {
            $this->vista('auth/restablecer-password', ['token' => null, 'error' => 'El enlace no es válido o ya expiró. Solicita uno nuevo.']);
            return;
        }

        $clave = $_POST['contrasena'] ?? '';
        $confirmar = $_POST['confirmar_contrasena'] ?? '';

        if (strlen($clave) < 8 || !preg_match('/[A-Za-z]/', $clave) || !preg_match('/[0-9]/', $clave)) {
            $this->vista('auth/restablecer-password', ['token' => $token, 'error' => 'La contraseña debe tener mínimo 8 caracteres y combinar letras y números.']);
            return;
        }

        if ($clave !== $confirmar) {
            $this->vista('auth/restablecer-password', ['token' => $token, 'error' => 'Las contraseñas no coinciden.']);
            return;
        }

        $this->usuarioModel->cambiarContrasena((int) $usuario['id'], $clave);
        $this->usuarioModel->marcarTokenUsado($token);

        $_SESSION['mensaje'] = 'Contraseña restablecida correctamente. Ya puedes iniciar sesión.';
        $this->redireccionar('/auth');
    }
}