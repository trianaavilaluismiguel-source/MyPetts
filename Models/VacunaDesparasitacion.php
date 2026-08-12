<?php
require_once __DIR__ . '/Model.php';

class VacunaDesparasitacion extends Model
{
    protected string $tabla = 'vacunas_desparasitaciones';

    // HU-05 Esc.4: registrar una vacuna o desparasitación aplicada
    public function registrar(array $datos): string
    {
        return $this->crear([
            'mascota_id'          => $datos['mascota_id'],
            'historial_id'        => $datos['historial_id'] ?? null,
            'veterinario_id'      => $datos['veterinario_id'],
            'tipo'                => $datos['tipo'],
            'nombre_producto'     => $datos['nombre_producto'],
            'lote'                => $datos['lote'],
            'fecha_aplicacion'    => $datos['fecha_aplicacion'],
            'fecha_proxima_dosis' => $datos['fecha_proxima_dosis'],
        ]);
    }

    // HU-05 Esc.4/Esc.5: historial de vacunas/desparasitaciones de una mascota,
    // con el estado de alerta ya calculado (vencida / proxima / vigente)
    public function buscarPorMascota(int $mascotaId): array
    {
        $stmt = $this->db->prepare(
            "SELECT v.*,
                CASE
                    WHEN v.fecha_proxima_dosis < CURDATE() THEN 'vencida'
                    WHEN v.fecha_proxima_dosis <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'proxima'
                    ELSE 'vigente'
                END AS estado_alerta
            FROM vacunas_desparasitaciones v
            WHERE v.mascota_id = :mascota_id
            ORDER BY v.fecha_aplicacion DESC"
        );
        $stmt->execute(['mascota_id' => $mascotaId]);
        return $stmt->fetchAll();
    }

    // HU-05 Esc.5: todas las vacunas/desparasitaciones vencidas o próximas a vencer (≤30 días),
    // sin importar la mascota — útil para un panel de alertas general
    public function buscarAlertas(): array
    {
        $stmt = $this->db->query(
            "SELECT v.*, m.nombre AS nombre_mascota, m.dueno_id,
                CASE
                    WHEN v.fecha_proxima_dosis < CURDATE() THEN 'vencida'
                    ELSE 'proxima'
                END AS estado_alerta
            FROM vacunas_desparasitaciones v
            INNER JOIN mascotas m ON m.id = v.mascota_id
            WHERE v.fecha_proxima_dosis <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            ORDER BY v.fecha_proxima_dosis ASC"
        );
        return $stmt->fetchAll();
    }

    // HU-05 Esc.4: traduce el ENUM 'tipo' a un texto legible
    public static function etiquetaTipo(string $valor): string
    {
        return match ($valor) {
            'vacuna' => 'Vacuna',
            'desparasitacion' => 'Desparasitación',
            default => $valor,
        };
    }

    // HU-05 Esc.5: color sugerido para la vista según el estado de alerta
    public static function colorAlerta(string $estado): string
    {
        return match ($estado) {
            'vencida' => 'red',
            'proxima' => '#b8860b', // amarillo oscuro, legible sobre fondo blanco
            default => 'inherit',
        };
    }
}