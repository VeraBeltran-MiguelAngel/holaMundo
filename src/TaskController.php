<?php
class TaskController
{
    /**
     * Inyeccion de dependencias por constructor ya que necesita un objeto de la
     * clase TaskGateway (en vez de crear uno aqui lo pasamos al constructor)
     * @param gateway objeto de la clase TaskGateway
     */
    public function __construct(private TaskGateway $gateway)
    {
    }
    /**
     * funcion para manejar la peticon y decidir que hacer dependiendo del metodo
     * @param method metodo de la peticion
     * @param id identifica el reurso unico (acepta null) con el signo'?'
     * @return void (no retorna nada)   
     */
    public function processRequest(string $method, ?string $id): void
    {
        //*cuando la ruta no contiene ID (insertar o mostrar todos los registros)
        if ($id === null) {
            if ($method == "GET") {
                //listar registros de la tabla task accedemos al getAll del objeto gateway como retorna un arreglo lo convertimos a JSON
                echo json_encode($this->gateway->getAll());
            } elseif ($method == "POST") {
                //crear empleado, decodificamos el json y lo transforma a un arreglo asociativo, 
                //si envias informacion erronea devuelve null, pero necesitamos devolver solo un arreglo vacio por eso hacemos un cast a array simple
                $data=(array)json_decode(file_get_contents("php://input"),true);
                var_dump($data);
            } else {
                //metodos que estan permitidos
                $this->respondMethodNotAllowed("GET, POST");
            }
        }
        //*cuando la ruta si tiene un ID (buscas un registro especifico o vas a editar o eliminar)
        else {
            //Debemos validar que el ID  que si existe en el url , exista en la tabla empleados de la BD
            $empleado = $this->gateway->get($id);

            if ($empleado === false) {
                //si el id del empleado no existe  llamamamos al metodo not found
                $this->respondNotFound($id);
                //salimos del metodo processRequest para no continuar evaluando
                return;
            }
            //quiere decir que si existe la task (id) y agregamos un switch para ver que hacer
            switch ($method) {
                    //si es get solo mostramos el empleado que coincida con el ID
                case 'GET':
                    echo json_encode($empleado);
                    break;
                    //si es patch editamos la task especifica
                case 'PATCH':
                    echo "update $id";
                    break;
                    // eliminamos la task especifica
                case 'DELETE':
                    echo "delete $id";
                    break;
                default:
                    // si mandas un post con id te dice que solo esta permitido get,patch y delete
                    $this->respondMethodNotAllowed("GET, PATCH, DELETE");
            }
        }
    }
    /**
     * funcion para manejar los metodos que estan permitidos
     */
    private function respondMethodNotAllowed(string $allowed_methods): void
    {
        // si envias otro metodo diferente a la url http://localhost/holamundo/api/empleados debe mostrar error
        http_response_code(405);
        //y enviamos al header los metodos que estan permitidos
        header("Allow: $allowed_methods");
    }
    /**
     * Metodo que envia un not found si el id que pasas en la url no existe en la tabla empleados de la BD
     */
    private function respondNotFound(string $id): void
    {
        // si envias un id que no existe muestra el error 404
        http_response_code(404);
        echo json_encode(["mensaje" => "Empleado con id: $id no encontrado"]);
    }
}
