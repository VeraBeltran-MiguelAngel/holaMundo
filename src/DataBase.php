<?php
class DataBase
{
    //metodo constructor
    public function __construct(private string $host, private string $name, private string $user, private string $password)
    {
    }
    //metodo para conectarse a la base
    public function getConnection(): PDO
    {
        //usaremos PDO (php data object) ver documentacion
        //data source name
        $dsn = "mysql:host={$this->host};dbname={$this->name};charset=utf8";
        //creamos objeto PDO pasamos usuario y contraseña y un error exc si ocurre error al conectarse
        return new PDO($dsn, $this->user, $this->password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }
}
