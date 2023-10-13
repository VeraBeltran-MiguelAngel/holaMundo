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
        $sql = "SELECT * FROM empleados ORDER BY nombre";
        //devuelve un PDOstatement object
        $stmt = $this->conn->query($sql);
        //devolvemos las filas de la consulta en un arreglo (tal y como estan los valores en la BD)
        // return $stmt->fetchAll(PDO::FETCH_ASSOC);
        //si deseas dar formato a los valores que se muestran en el json, en este 
        //caso los 1 y 0 del atributo activo seran presentados como true o false
        $data=[]; 

        //obtener el registro como un arreglo asociativo para que retorne un registro individual
        //recorremos los registros que devuelve el statement cuando ya no hay da false y rompe el ciclo
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            //accedemos al atributo activo del arreglo asociativo y lo convertimos a booleano
            $row ['activo'] = (bool) $row ['activo'];
            //guardamos el registro modificado
            $data[]=$row;
        }
         //retornamos el arreglo con los valores modificados
        return $data;
    }
}
