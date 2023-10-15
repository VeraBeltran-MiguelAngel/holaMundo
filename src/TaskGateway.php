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
     * @return data de tipo arreglo
     */
    public function getAll(): array
    {
        //aqui te pueden hacer sql injection
        $sql = "SELECT * FROM empleados ORDER BY id";
        //devuelve un PDOstatement object
        $stmt = $this->conn->query($sql);
        //devolvemos las filas de la consulta en un arreglo (tal y como estan los valores en la BD)
        // return $stmt->fetchAll(PDO::FETCH_ASSOC);
        //si deseas dar formato a los valores que se muestran en el json, en este 
        //caso los 1 y 0 del atributo activo seran presentados como true o false
        $data = [];

        //obtener el registro como un arreglo asociativo para que retorne un registro individual
        //recorremos los registros que devuelve el statement cuando ya no hay da false y rompe el ciclo
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            //accedemos al atributo activo del arreglo asociativo y lo convertimos a booleano
            $row['activo'] = (bool) $row['activo'];
            //guardamos el registro modificado
            $data[] = $row;
        }
        //retornamos el arreglo con los valores modificados
        return $data;
    }

    /**
     * Mostrar un unico registro dependiendo el ID
     * @param id 
     * @return data puede ser de tipo arreglo o falso en caso de no encontrar coincidencias
     */
    public function get(string $id): array | false
    {
        //!para evitar sql injection
        $sql = "SELECT * FROM empleados WHERE id=:id";
        // crear el statemnet
        $stmt = $this->conn->prepare($sql);

        //bindValuevinula un valor a un parametro
        //para vincular el id placeholder (argumento en la consulta) CON el parametro de la funcion
        //debe ser insertado en el SQL string como integer
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        //ejecutar statemnt
        $stmt->execute();
        //tener los datos en un array asociativo (retorna falso en caso de no tener registros)
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        //si encuentra un registro que coincida con el id
        if ($data !== false) {
            //convertimos el atributo activo a boolean
            $data['activo'] = (bool) $data['activo'];
        }
        //retornamos el registro transformado
        return $data;
    }

    /**
     * Metodo para insertar empleados
     * @param data recibe los datos ingresados por el usuario en un arreglo
     * @return string devuelve el id del ultimo registro como string
     */
    public function create(array $data): string
    {
        $sql = "INSERT INTO empleados (nombre, correo, activo, rango)
        VALUES (:nombre, :correo, :activo, :rango)";

        $stmt = $this->conn->prepare($sql);
        //vincular el parametro nombre con el  valor que viene del arreglo se vincula de tipo string
        $stmt->bindValue(":nombre", $data["nombre"], PDO::PARAM_STR);

        //vincular el correo
        $stmt->bindValue(":correo", $data["correo"], PDO::PARAM_STR);


        //si el rango es nulo (configuramos este atributo para que acepte nulos en la BD)
        if (empty($data["rango"])) {
            //vinculamos el parametro a un valor nulo
            $stmt->bindValue(":rango", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(":rango", $data["rango"], PDO::PARAM_INT);
        }

        //el atributo activo(por defecto falso) es boolean por lo tanto se vincula a aun boolean
        //el simbolo ?? asigna un false por defecto si viene vacio
        $stmt->bindValue(":activo", $data["activo"] ?? false, PDO::PARAM_BOOL);

        $stmt->execute();

        //devoovemos el id del registro que acaba de ser insertado por defecto regresa un string
        return $this->conn->lastInsertId();
    }

    /**
     * Metodo para actualizar empleados
     * @param id empleado a actualizar
     * @param data nuevos datos
     * @return int devuelve las cantidad de filas que fueron actualizadas
     */

    public function update(string $id, array $data): int
    {
        $fields = [];
        //si el nombre no esta vacio agregamos lo añadimos añ arreglo de campos
        if (!empty($data["nombre"])) {
            //al atributo nombre le asignamos el "nombre" que viene como parametro de tipo string
            $fields["nombre"] = [$data["nombre"], PDO::PARAM_STR];
        }

        //comprueba el correo
        if (!empty($data["correo"])) {
            $fields["correo"] = [$data["correo"], PDO::PARAM_STR];
        }

        //comprobar que el key activo no este vacio en el arreglo asociativo
        if (array_key_exists("activo", $data)) {
            //al atributo activo le asignamos el "activo" que viene como parametro de tipo bool
            $fields["activo"] = [$data["activo"], PDO::PARAM_BOOL];
        }

        //comprobar que el key rango que no este vacio (por default acepta nulos)
        if (array_key_exists("rango", $data)) {
            //al atributo rango le asignamos el "rango" que viene como parametro de tipo int
            // (operador ternario ? ) si el rango viene como nulo especificamos el tipo de dato nulo
            // de lo contrario colocamos un tipo entero
            $fields["rango"] = [$data["rango"], $data["rango"] === null ? PDO::PARAM_NULL : PDO::PARAM_INT];
        }

        //para evitar construir una consulta SQL con campos vacios
        //ejemplo de consulta construida UPDATE empleados SET nombre = :nombre, correo = :correo, activo = :activo, rango = :rango WHERE id = :id
        if (empty($fields)) {
            // si no hay filas para actualizar retornamos cero
            return 0;
        } else {
            //si los campos a actualizar no estan vacios continuamos a construir el string de la actualizacion
            //queremos un set statement para cada columna
            $sets = array_map(function ($value) {
                return "$value = :$value";
                //accedemos a los keys del arreglo fiel que ya contiene datos
            }, array_keys($fields));

            $sql = "UPDATE empleados"
                . " SET " . implode(", ", $sets)
                . " WHERE id = :id";

            // Crear statement de CONSULTA
            $stmt = $this->conn->prepare($sql);
            //vincular el valor del id con el parametro
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);

            //para los demas parametros tenemos que usar el arreglo field
            foreach ($fields as $name => $values) {
                //valor que queremos vincular , tipo de dato PDO, valores que añadimos cuando creamos el arreglo anteriormente
                $stmt->bindValue(":$name", $values[0], $values[1]);
            }

            //ejecutar consulta
            $stmt->execute();
            //retornamos el numero de filas que hemos actualizado
            return $stmt->rowCount();
        }
    }

    /**
     * Metodo para eliminar empleado
     */

    public function delete(string $id): int
    {
        $sql = "DELETE FROM empleados
        WHERE id =:id";

        $stmt = $this->conn->prepare($sql);

        //vincular id del parametro como int
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        //devolvemos el numero de filas eliminadas
        return $stmt->rowCount();
    }
}
