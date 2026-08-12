<?php
require_once __DIR__ . '/Model.php';

class HistorialClinico extends Model
{
    protected string $tabla = 'historial_clinico';

    // HU-05 Esc.1: registrar una nueva entrada clínica
    public function registrarEntrada(array $datos): string
    {
        return $this->crear([
            'mascota_id'      => $datos['mascota_id'],
            'cita_id'         => $datos['cita_id'] ?? null,
            'veterinario_id'  => $datos['veterinario_id'],
            'motivo_consulta' => $datos['motivo_consulta'],
            'diagnostico'     => $datos['diagnostico'],
            'tratamiento'     => $datos['tratamiento'],
            'observaciones'   => $datos['observaciones'] ?? null,
        ]);
    }

    // HU-05 Esc.2/Esc.3: historial de una mascota, del más reciente al más antiguo
    public function buscarPorMascota(int $mascotaId): array
    {
        $stmt = $this->db->prepare(
            "SELECT h.*, u.nombre AS nombre_veterinario
            FROM historial_clinico h
            INNER JOIN usuarios u ON u.id = h.veterinario_id
            WHERE h.mascota_id = :mascota_id
            ORDER BY h.fecha_registro DESC"
        );
        $stmt->execute(['mascota_id' => $mascotaId]);
        return $stmt->fetchAll();
    }

    // HU-05 Esc.6: solo se puede editar dentro de las 24 horas siguientes al registro
    public function puedeEditar(array $entrada): bool
    {
        $fechaRegistro = new DateTime($entrada['fecha_registro']);
        $ahora = new DateTime();
        $horasTranscurridas = ($ahora->getTimestamp() - $fechaRegistro->getTimestamp()) / 3600;

        return $horasTranscurridas <= 24;
    }

    // HU-05 Esc.6: edita una entrada y deja registro de quién y cuándo lo hizo
    public function editarEntrada(int $id, array $datos, int $editorId): bool
    {
        return $this->actualizar($id, [
            'motivo_consulta'      => $datos['motivo_consulta'],
            'diagnostico'          => $datos['diagnostico'],
            'tratamiento'          => $datos['tratamiento'],
            'observaciones'        => $datos['observaciones'] ?? null,
            'fecha_ultima_edicion' => date('Y-m-d H:i:s'),
            'editado_por'          => $editorId,
        ]);
    }
}