<?php
require_once __DIR__ . '/Model.php';

class Usuario extends Model
{
    // Le decimos al Model base con qué tabla trabajar
    protected string $tabla = 'usuarios';

    // HU-01 Esc.2: verificar si el correo ya existe antes de registrar
    public function buscarPorCorreo(string $correo): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE correo = :correo LIMIT 1");
        $stmt->execute(['correo' => $correo]);
        return $stmt->fetch();
    }

    // HU-01: registrar un nuevo usuario con la contraseña ya encriptada (auto-registro, siempre DueñoMascota)
    public function registrar(string $nombre, string $correo, string $contrasenaPlana, string $telefono, int $rolId): string
    {
        $hash = password_hash($contrasenaPlana, PASSWORD_DEFAULT);

        return $this->crear([
            'nombre'          => $nombre,
            'correo'          => $correo,
            'contrasena_hash' => $hash,
            'telefono'        => $telefono,
            'rol_id'          => $rolId,
        ]);
    }

    // HU-02: verificar que la contraseña ingresada coincida con el hash guardado
    public function verificarContrasena(string $contrasenaPlana, string $hashGuardado): bool
    {
        return password_verify($contrasenaPlana, $hashGuardado);
    }

    // HU-04: lista los usuarios activos de un rol específico (ej: todos los Veterinarios)
    public function buscarPorRol(int $rolId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM usuarios WHERE rol_id = :rol_id AND activo = 1 ORDER BY nombre"
        );
        $stmt->execute(['rol_id' => $rolId]);
        return $stmt->fetchAll();
    }

    // HU-07: lista todos los usuarios, con el nombre del rol ya incluido (para la tabla del Administrador)
    public function listarConRol(): array
    {
        $stmt = $this->db->query(
            "SELECT u.*, r.nombre_rol
            FROM usuarios u
            INNER JOIN roles r ON r.id = u.rol_id
            ORDER BY u.nombre"
        );
        return $stmt->fetchAll();
    }

    // Devuelve el catálogo de roles, para llenar el <select> del formulario
    public function obtenerRoles(): array
    {
        $stmt = $this->db->query("SELECT * FROM roles ORDER BY id");
        return $stmt->fetchAll();
    }

    // HU-07: el Administrador crea una cuenta directamente, eligiendo el rol.
    // Se genera una contraseña temporal, y se obliga a cambiarla en el primer login.
    public function crearPorAdmin(string $nombre, string $correo, string $telefono, int $rolId, int $creadoPor): array
    {
        $contrasenaTemporal = substr(bin2hex(random_bytes(4)), 0, 8);
        $hash = password_hash($contrasenaTemporal, PASSWORD_DEFAULT);

        $id = $this->crear([
            'nombre'              => $nombre,
            'correo'              => $correo,
            'contrasena_hash'     => $hash,
            'telefono'            => $telefono,
            'rol_id'              => $rolId,
            'requiere_cambio_pwd' => 1,
            'creado_por'          => $creadoPor,
        ]);

        return ['id' => $id, 'contrasena_temporal' => $contrasenaTemporal];
    }

    // HU-07: edita los datos básicos de una cuenta (sin tocar la contraseña)
    public function actualizarDatos(int $id, string $nombre, string $correo, string $telefono, int $rolId): bool
    {
        return $this->actualizar($id, [
            'nombre'   => $nombre,
            'correo'   => $correo,
            'telefono' => $telefono,
            'rol_id'   => $rolId,
        ]);
    }

    // HU-07: activar o desactivar una cuenta
    public function cambiarEstado(int $id, bool $activo): bool
    {
        return $this->actualizar($id, ['activo' => $activo ? 1 : 0]);
    }

    // HU-07: resetea la contraseña a una temporal y obliga a cambiarla en el próximo login
    public function resetearContrasena(int $id): string
    {
        $contrasenaTemporal = substr(bin2hex(random_bytes(4)), 0, 8);
        $hash = password_hash($contrasenaTemporal, PASSWORD_DEFAULT);

        $this->actualizar($id, [
            'contrasena_hash'     => $hash,
            'requiere_cambio_pwd' => 1,
        ]);

        return $contrasenaTemporal;
    }

    // HU-07: cambia la contraseña y quita la marca de "cambio obligatorio"
    public function cambiarContrasena(int $id, string $contrasenaPlana): bool
    {
        $hash = password_hash($contrasenaPlana, PASSWORD_DEFAULT);

        return $this->actualizar($id, [
            'contrasena_hash'     => $hash,
            'requiere_cambio_pwd' => 0,
        ]);
    }

    // HU-02 Esc.5: genera un token de recuperación válido por 30 minutos
    public function generarTokenRecuperacion(int $usuarioId): string
    {
        $token = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        $stmt = $this->db->prepare(
            "INSERT INTO tokens_recuperacion (usuario_id, token, expira_en) VALUES (:usuario_id, :token, :expira_en)"
        );
        $stmt->execute(['usuario_id' => $usuarioId, 'token' => $token, 'expira_en' => $expira]);

        return $token;
    }

    // HU-02 Esc.5: busca un token válido (no usado, no vencido) y devuelve el usuario asociado
    public function buscarPorTokenValido(string $token): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT u.* FROM tokens_recuperacion t
             INNER JOIN usuarios u ON u.id = t.usuario_id
             WHERE t.token = :token AND t.usado = 0 AND t.expira_en >= NOW()
             LIMIT 1"
        );
        $stmt->execute(['token' => $token]);
        return $stmt->fetch();
    }

    // Marca un token de recuperación como usado (evita reutilizarlo)
    public function marcarTokenUsado(string $token): void
    {
        $stmt = $this->db->prepare("UPDATE tokens_recuperacion SET usado = 1 WHERE token = :token");
        $stmt->execute(['token' => $token]);
    }
}