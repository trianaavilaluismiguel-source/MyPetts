<?php
//conectar con la base de datos
class Conexion
{
    private $host    = 'localhost';
    private $dbname  = 'mypetts';
    private $port = '3306';
    private $usuario = 'admin';
    private $clave   = 'root';
    

    public function conectar()
    {
        $dsn = "mysql:host=$this->host;dbname=$this->dbname;charset=utf8mb4";

        try {
            $pdo = new PDO($dsn, $this->usuario, $this->clave);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $pdo;

        } catch (PDOException $error) {
            die('Error al conectar: ' . $error->getMessage());
        }
    }
}