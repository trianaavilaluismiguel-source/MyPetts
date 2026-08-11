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

        // Esc.2: el correo no existe -> mensaje genérico (no revelamos si el correo existe o no)
        if (!$usuario) {
            $this->vista('auth/login', ['error' => 'Acceso denegado. El correo o la contraseña son incorrectos. Intente nuevamente.']);
            return;
        }

        // Esc.4: cuenta bloqueada temporalmente (se revisa ANTES de validar la contraseña)
        if ($usuario['bloqueado_hasta'] && strtotime($usuario['bloqueado_hasta']) > time()) {
            $this->vista('auth/login', ['error' => 'Cuenta bloqueada temporalmente. Intenta de nuevo en unos minutos.']);
            return;
        }

        // Esc.2: contraseña incorrecta
        if (!$this->usuarioModel->verificarContrasena($clave, $usuario['contrasena_hash'])) {

            // Sumamos 1 al contador de intentos fallidos
            $intentos = $usuario['intentos_fallidos'] + 1;

            if ($intentos >= 3) {
                // Se alcanzó el máximo: bloqueamos por 5 minutos y reiniciamos el contador
                $this->usuarioModel->actualizar($usuario['id'], [
                    'intentos_fallidos' => 0,
                    'bloqueado_hasta'   => date('Y-m-d H:i:s', strtotime('+5 minutes')),
                ]);

                $this->vista('auth/login', ['error' => 'Has superado el número de intentos permitidos. Cuenta bloqueada por 5 minutos.']);
                return;
            }

            // Aún no llega a 3: solo guardamos el nuevo contador
            $this->usuarioModel->actualizar($usuario['id'], ['intentos_fallidos' => $intentos]);

            $this->vista('auth/login', ['error' => 'Acceso denegado. El correo o la contraseña son incorrectos. Intente nuevamente.']);
            return;
        }

        // Esc.3: cuenta desactivada
        if (!$usuario['activo']) {
            $this->vista('auth/login', ['error' => 'Tu cuenta está desactivada. Contacta al administrador de la clínica.']);
            return;
        }

        // Login correcto: reinicia intentos fallidos y guarda la sesión
        $this->usuarioModel->actualizar($usuario['id'], ['intentos_fallidos' => 0, 'bloqueado_hasta' => null]);

        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre']     = $usuario['nombre'];
        $_SESSION['rol_id']     = $usuario['rol_id'];

        $this->redireccionar('/dashboard');
    }

    public function logout(): void
    {
        session_start();
        session_destroy();
        $this->redireccionar('/auth');
    }
}
