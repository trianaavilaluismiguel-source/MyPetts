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
}