<?php
require_once __DIR__ . '/Model.php';

class Mascota extends Model
{
    protected string $tabla = 'mascotas';

    // Devuelve solo las mascotas ACTIVAS de un dueño específico
    public function buscarPorDueno(int $duenoId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->tabla} WHERE dueno_id = :dueno_id AND activa = 1 ORDER BY nombre"
        );
        $stmt->execute(['dueno_id' => $duenoId]);
        return $stmt->fetchAll();
    }

    // Devuelve todas las mascotas activas (para uso de Veterinario/Recepcionista/Administrador)
    public function todosActivos(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->tabla} WHERE activa = 1 ORDER BY nombre");
        return $stmt->fetchAll();
    }

    // Baja lógica: en vez de eliminar el registro, lo marca como inactivo
    public function darDeBaja(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->tabla} SET activa = 0 WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    // Reactivar una mascota dada de baja (opcional, útil para corregir errores)
    public function reactivar(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->tabla} SET activa = 1 WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    // HU-03 Esc.6: búsqueda/filtro por nombre, especie o número de registro (id)
    public function buscar(string $termino): array
    {
        $like = '%' . $termino . '%';

        // Si el término escrito es numérico, también buscamos coincidencia exacta por id
        if (ctype_digit($termino)) {
            $stmt = $this->db->prepare(
                "SELECT * FROM {$this->tabla} 
                 WHERE activa = 1 AND (nombre LIKE :like OR especie LIKE :like OR id = :id_exacto)
                 ORDER BY nombre"
            );
            $stmt->execute(['like' => $like, 'id_exacto' => $termino]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT * FROM {$this->tabla} 
                 WHERE activa = 1 AND (nombre LIKE :like OR especie LIKE :like)
                 ORDER BY nombre"
            );
            $stmt->execute(['like' => $like]);
        }

        return $stmt->fetchAll();
    }

    // HU-03 Esc.4: traduce el valor del ENUM a un texto legible para mostrar en el listado
    public static function etiquetaEstadoSalud(string $valor): string
    {
        return match ($valor) {
            'al_dia' => 'Al día',
            'pendiente_atencion' => 'Pendiente de atención',
            default => $valor,
        };
    }

    // HU-03 Esc.4: calcula la edad en años/meses a partir de la fecha de nacimiento
    public static function calcularEdad(?string $fechaNacimiento): string
    {
        if (empty($fechaNacimiento)) {
            return 'N/A';
        }

        try {
            $nacimiento = new DateTime($fechaNacimiento);
        } catch (Exception $e) {
            return 'N/A';
        }

        $hoy = new DateTime();
        $diferencia = $hoy->diff($nacimiento);

        if ($diferencia->y >= 1) {
            return $diferencia->y . ' ' . ($diferencia->y === 1 ? 'año' : 'años');
        }

        return $diferencia->m . ' ' . ($diferencia->m === 1 ? 'mes' : 'meses');
    }
}