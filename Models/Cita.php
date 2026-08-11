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

    // HU-01: registrar un nuevo usuario con la contraseña ya encriptada
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
}