<?php
require_once __DIR__ . '/Model.php';

class Cita extends Model
{
    protected string $tabla = 'citas';

    public function buscarPorDueno(int $duenoId): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, m.nombre AS nombre_mascota, u.nombre AS nombre_veterinario
            FROM citas c
            INNER JOIN mascotas m ON m.id = c.mascota_id
            INNER JOIN usuarios u ON u.id = c.veterinario_id
            WHERE m.dueno_id = :dueno_id
            ORDER BY c.fecha DESC, c.hora DESC"
        );
        $stmt->execute(['dueno_id' => $duenoId]);
        return $stmt->fetchAll();
    }

    public function buscarPorVeterinario(int $veterinarioId): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, m.nombre AS nombre_mascota, d.nombre AS nombre_dueno
            FROM citas c
            INNER JOIN mascotas m ON m.id = c.mascota_id
            INNER JOIN usuarios d ON d.id = m.dueno_id
            WHERE c.veterinario_id = :veterinario_id
            ORDER BY c.fecha DESC, c.hora DESC"
        );
        $stmt->execute(['veterinario_id' => $veterinarioId]);
        return $stmt->fetchAll();
    }

    public function todas(): array
    {
        $stmt = $this->db->query(
            "SELECT c.*, m.nombre AS nombre_mascota, v.nombre AS nombre_veterinario
            FROM citas c
            LEFT JOIN mascotas m ON m.id = c.mascota_id
            LEFT JOIN usuarios v ON v.id = c.veterinario_id
            ORDER BY c.fecha DESC, c.hora DESC"
        );
        return $stmt->fetchAll();
    }

    public function horarioDisponible(int $veterinarioId, string $fecha, string $hora): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id FROM citas
            WHERE veterinario_id = :veterinario_id
            AND fecha = :fecha
            AND hora = :hora
            AND estado != 'cancelada'
            LIMIT 1"
        );
        $stmt->execute([
            'veterinario_id' => $veterinarioId,
            'fecha' => $fecha,
            'hora' => $hora,
        ]);

        return $stmt->fetch() === false;
    }

    // HU-04 Esc.2: busca hasta 3 horarios libres, empezando por la fecha pedida
    // y, si ese día no alcanza, sigue buscando día por día hasta 14 días adelante
    public function sugerirHorariosLibres(int $veterinarioId, string $fechaInicio): array
    {
        $horasDisponibles = [
            '08:00:00', '09:00:00', '10:00:00', '11:00:00', '12:00:00',
            '13:00:00', '14:00:00', '15:00:00', '16:00:00', '17:00:00'
        ];

        $sugerencias = [];
        $fecha = new DateTime($fechaInicio);

        for ($diasAdelante = 0; $diasAdelante <= 14; $diasAdelante++) {
            $fechaActual = (clone $fecha)->modify("+{$diasAdelante} days")->format('Y-m-d');

            foreach ($horasDisponibles as $hora) {
                if ($this->horarioDisponible($veterinarioId, $fechaActual, $hora)) {
                    $sugerencias[] = ['fecha' => $fechaActual, 'hora' => $hora];
                }

                if (count($sugerencias) >= 3) {
                    return $sugerencias;
                }
            }
        }

        return $sugerencias;
    }

    public function cancelar(int $id, string $motivo): bool
    {
        return $this->actualizar($id, [
            'estado' => 'cancelada',
            'motivo_cancelacion' => $motivo,
        ]);
    }

    // Dashboard: próximas citas agendadas de un Dueño (para su resumen de inicio)
    public function proximasPorDueno(int $duenoId, int $limite = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, m.nombre AS nombre_mascota, u.nombre AS nombre_veterinario
            FROM citas c
            INNER JOIN mascotas m ON m.id = c.mascota_id
            INNER JOIN usuarios u ON u.id = c.veterinario_id
            WHERE m.dueno_id = :dueno_id AND c.estado = 'agendada' AND c.fecha >= CURDATE()
            ORDER BY c.fecha ASC, c.hora ASC
            LIMIT " . (int) $limite
        );
        $stmt->execute(['dueno_id' => $duenoId]);
        return $stmt->fetchAll();
    }

    // Dashboard: próximas citas asignadas a un Veterinario
    public function proximasPorVeterinario(int $veterinarioId, int $limite = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, m.nombre AS nombre_mascota, d.nombre AS nombre_dueno
            FROM citas c
            INNER JOIN mascotas m ON m.id = c.mascota_id
            INNER JOIN usuarios d ON d.id = m.dueno_id
            WHERE c.veterinario_id = :veterinario_id AND c.estado = 'agendada' AND c.fecha >= CURDATE()
            ORDER BY c.fecha ASC, c.hora ASC
            LIMIT " . (int) $limite
        );
        $stmt->execute(['veterinario_id' => $veterinarioId]);
        return $stmt->fetchAll();
    }

    // Dashboard: agenda general próxima (para Recepcionista/Administrador)
    public function proximasTodas(int $limite = 8): array
    {
        $stmt = $this->db->query(
            "SELECT c.*, m.nombre AS nombre_mascota, v.nombre AS nombre_veterinario
            FROM citas c
            LEFT JOIN mascotas m ON m.id = c.mascota_id
            LEFT JOIN usuarios v ON v.id = c.veterinario_id
            WHERE c.estado = 'agendada' AND c.fecha >= CURDATE()
            ORDER BY c.fecha ASC, c.hora ASC
            LIMIT " . (int) $limite
        );
        return $stmt->fetchAll();
    }

    // HU-04 Esc.7: reagendar NO sobrescribe la cita original.
    // Crea una fila nueva (enlazada por cita_origen_id) y marca la original como 'reagendada',
    // así se conserva el historial completo de cambios de una cita.
    public function reagendar(array $citaOriginal, string $nuevaFecha, string $nuevaHora): string
    {
        $nuevoId = $this->crear([
            'mascota_id'     => $citaOriginal['mascota_id'],
            'veterinario_id' => $citaOriginal['veterinario_id'],
            'agendada_por'   => $citaOriginal['agendada_por'],
            'fecha'          => $nuevaFecha,
            'hora'           => $nuevaHora,
            'tipo_consulta'  => $citaOriginal['tipo_consulta'],
            'estado'         => 'agendada',
            'cita_origen_id' => $citaOriginal['id'],
        ]);

        $this->actualizar($citaOriginal['id'], ['estado' => 'reagendada']);

        return $nuevoId;
    }
}