<?php

class MySqlDatabase
{
    private $connection;

    public function __construct($host, $username, $password, $database)
    {
        $this->connection = mysqli_connect($host, $username, $password, $database);
        if (!$this->connection) {
            die("Connection failed: " . mysqli_connect_error());
        }
    }

    public function query($sql)
    {
        $result = mysqli_query($this->connection, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function prepare($sql)
    {
        // Prepara una consulta SQL y devuelve un objeto de sentencia preparada
        return $this->connection->prepare($sql);
    }

    public function execute($sql)
    {
        mysqli_query($this->connection, $sql);
    }

    public function __destruct()
    {
        mysqli_close($this->connection);
    }
}
