<?php
class TaskController
{
    /**
     * funcion para manejar la peticon y decidir que hacer dependiendo del metodo
     * @param method metodo de la peticion
     * @param id identifica el reurso unico (acepta null) con el signo'?'
     * @return void (no retorna nada)
     */
    public function processRequest(string $method, ?string $id): void
    {
        if ($id === null) {
            if ($method == "GET") {
                //debemos mostrar las task
                echo "index";
            } elseif ($method == "POST") {
                //crear task
                echo "create";
            } else {
                //metodos que estan permitidos
                $this->respondMethodNotAllowed("GET, POST");
            }
        } else {
            //quiere decir que si existe la task (id) y agregamos un switch para ver que hacer
            switch ($method) {
                    //si es get solo mostramos la task especifica
                case 'GET':
                    echo "show $id";
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
        // si envias otro metodo diferente a la url http://localhost/holamundo/api/task debe mostrar error
        http_response_code(405);
        //y enviamos al header los metodos que estan permitidos
        header("Allow: $allowed_methods");
    }
}
