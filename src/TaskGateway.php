<?php

/**Esta  clase necesita acceso a la BD por lo tanto necesita un objeto de la clase BD
 * en vez de crear uno aqui vamos a inyectar esta dependencia pasando el objeto en el constructor
 * (como si fuera un modelo entity con una clase de servicio (aqui estaria integrado el DAO con el service))
 */
class TaskGateway
{
    /**
     * Inyeccion de dependencias por constructor, todos los metodos publicos
     * de esta clase necesitaran conectarse a la BD
     * @param objeto de la clase DataBase
     */
    private PDO $conn;   //propiedad privada para almacenar la conexion
    public function __construct(DataBase $database)
    {
        //llamar al metodo getConnection y lo guardamos en la propiedad privada
        $this->conn = $database->getConnection();
    }

    /**
     * Mostrar registros tabla task
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM task ORDER BY name";
        //devuelve un PDOstatement object
        $stmt = $this->conn->query($sql);
        //devolvemos las filas de la consulta en un arreglo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
