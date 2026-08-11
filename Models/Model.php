<?php
require_once __DIR__ . '/../Database/Conexion.php';

abstract class Model
{
    protected PDO $db;
    protected string $tabla;

    public function __construct()
    {
        $conexion = new Conexion();
        $this->db = $conexion->conectar();
    }

    public function todos(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->tabla}");
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->tabla} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function crear(array $datos): string
    {
        $columnas = implode(', ', array_keys($datos));
        $marcadores = ':' . implode(', :', array_keys($datos));
        $stmt = $this->db->prepare("INSERT INTO {$this->tabla} ($columnas) VALUES ($marcadores)");
        $stmt->execute($datos);
        return $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $set = implode(', ', array_map(fn($col) => "$col = :$col", array_keys($datos)));
        $datos['id'] = $id;
        $stmt = $this->db->prepare("UPDATE {$this->tabla} SET $set WHERE id = :id");
        return $stmt->execute($datos);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->tabla} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}