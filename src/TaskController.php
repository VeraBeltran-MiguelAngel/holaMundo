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
                $data = (array)json_decode(file_get_contents("php://input"), true);
                //devolvemos un mensaje de error si los datos a insertar son invalidos
                $errors = $this->getValidationErrors($data);

                // si los errores no estan vacios quiere decir que si los hay
                if (!empty($errors)) {
                    //llamar al metodo que maneja los errores
                    $this->respondUnprocessableEntity($errors);
                    //aborta el insert con datos invalidos
                    return;
                }
                //insertamos el nuevo registro y guardamos su id por que el create devuelve un id
                $id =  $this->gateway->create($data);
                //mensaje de exito
                $this->respondCreated($id);
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
                    //si es patch editamos tabla empleado
                case 'PATCH':
                    // actualizar empleado, decodificamos el json y lo transforma a un arreglo asociativo, 
                    //si envias informacion erronea devuelve null, pero necesitamos devolver solo un arreglo vacio por eso hacemos un cast a array simple
                    $data = (array)json_decode(file_get_contents("php://input"), true);
                    //devolvemos un mensaje de error si los datos a actualizar son invalidos, false por que es un registro ya existente
                    $errors = $this->getValidationErrors($data,false);
                    // si los errores no estan vacios quiere decir que si los hay
                    if (!empty($errors)) {
                        //llamar al metodo que maneja los errores
                        $this->respondUnprocessableEntity($errors);
                        //aborta el update con datos invalidos
                        return;
                    }
                    //llamar al metodo actualizar 
                    $this->gateway->update($id,$data);
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
     * Metodo para indicar que los datos no son procesables para la tabla
     */
    private function respondUnprocessableEntity(array $errors): void
    {
        http_response_code(422);
        //para el cuerpo de la respuesta enviamos el json con los errores de validacion
        echo json_encode(["errors" => $errors]);
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

    /**
     * Metodo que envia el estatus creado
     */

    private function respondCreated(string $id): void
    {
        // registro creado
        http_response_code(201);
        echo json_encode(["mensaje" => "Empleado con id: $id creado exitosamente"]);
    }

    /**
     * Validar datos antes de insertar si son erroneos
     * Si va a actualizar debe identificar que no es un nuevo registro y no debe pedir campos obligatorios
     * es decir si quieres actualizar solo un campo te pedira el atributo nombre por que es obligatorio
     * @param data recibe la info a validar
     * @param isNew verifica si es un nuevo registro o uno existente
     */
    private function getValidationErrors(array $data, bool $isNew = true): array
    {
        $errors = [];

        //validar los campos que deben ser llenados, si es nuevo registro pide el nombre obligatoriamente
        if ($isNew && empty($data["nombre"])) {
            $errors[] = "el nombre no debe ser nulo";
        }
        //validar que el rango no este vacio
        if (!empty($data["rango"])) {
            //como no es vacio validamos que sea integer
            if (filter_var($data["rango"], FILTER_VALIDATE_INT) === false) {
                $errors[] = "el rango debe ser un entero";
            }
        }

        return $errors;
    }
}
