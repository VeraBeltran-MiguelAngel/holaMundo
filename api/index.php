<?php
//configuramos el strict type cheking para manejar bien los errores(como esta en el index se aplica global)
declare(strict_types=1);
//mostrar errores
// ini_set("display_errors", "On"); lo comentamos para tener un error handler

//cargamos nuestras clase controladoras automaticamente con composer autoload
require dirname(__DIR__) . "/vendor/autoload.php";

//especificamos como manejar los errores y usamos el metodo referenciado 
set_exception_handler("ErrorHandler::handlerException");



//imprime el url de nuestro archivo (Obtinene solo el path no importa lo que coloques en la url)
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

//usamos la funcion explode para dividir en segmentos usando '/'como separador
$parts = explode("/", $path);
// print_r($parts); en vez de imprimir creamos una variable con el 4° elemento
$resource = $parts[3]; //task 
$id = $parts[4] ?? null; //id de la task si no hay coloca null

//si el resource es otra liga diferente a task debe arrojar un status 400
if ($resource != "task") {
    // header("{$_SERVER['SERVER_PROTOCOL']} 404 Not found"); primera opcion
    http_response_code(404); // 2° forma
    exit;
}

//debemos configurar el content type del contenido de la respuesta (JSON)
header("Content-type: application/json; charset=UTF-8");
//creamos un objeto de la clase
$controller = new TaskController;
//llamamos al metodo(process) de la clase y colocamos sus parametros (metodo usado,id)
$controller->processRequest($_SERVER['REQUEST_METHOD'], $id);
