<?php

class Conexion
{
    private string $host;
    private string $dbname;
    private string $port;
    private string $usuario;
    private string $clave;

    public function __construct()
    {
        $config = require __DIR__ . '/config.php';

        $this->host = $config['host'];
        $this->dbname = $config['dbname'];
        $this->port = $config['port'];
        $this->usuario = $config['usuario'];
        $this->clave = $config['clave'];
    }

    public function conectar(): PDO
    {
        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset=utf8mb4";

        try {
            $pdo = new PDO($dsn, $this->usuario, $this->clave, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            return $pdo;
        } catch (PDOException $error) {
            die('Error al conectar con la base de datos: ' . $error->getMessage());
        }
    }
}