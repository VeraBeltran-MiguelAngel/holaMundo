<?php
class TaskController
{
    /**
     * funcion para manejar la peticon y decidir que hacer dependiendo del metodo
     * @param method metodo de la peticion
     * @param id identifica el reurso unico (acepta null) con el signo'?'
     * @return void (no retorna nada)
     */
    public function processRequest(string $method, string $id):void
    {
        if ($id === null) {
            if ($method == "GET") {
                //debemos mostrar las task
                echo "index";
            } elseif ($method == "POST") {
                //crear task
                echo "create";
            }
        } else {
            //quiere decir que si existe la task y agregamos un switch para ver que hacer
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
            }
        }
    }
}
