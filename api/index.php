<?php

//configuramos el strict type cheking para manejar bien los errores(como esta en el index se aplica global)
declare(strict_types=1);
header("Access-Control-Allow-Origin: *");
//mostrar errores
// ini_set("display_errors", "On"); lo comentamos para tener un error handler

//cargamos nuestras clase controladoras automaticamente con composer autoload
require dirname(__DIR__) . "/vendor/autoload.php";

//especificamos como manejar los errores y usamos el metodo referenciado 
set_exception_handler("ErrorHandler::handlerException");

//creamos un objeto dotenv llamando al metodo createInmutable de la clase Dotenv (indicamos ruta de archivo)
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
//llamamos al metodo load en ese objeto para cargar los valores del env file
// en PHP $_ENV super global
$dotenv->load();




//imprime el url de nuestro archivo (Obtinene solo el path no importa lo que coloques en la url)
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

//usamos la funcion explode para dividir en segmentos usando '/'como separador
$parts = explode("/", $path);
// print_r($parts); en vez de imprimir creamos una variable con el 4° elemento
$resource = $parts[3]; //task 
$id = $parts[4] ?? null; //id de la task si no hay coloca null

//si el resource es otra liga diferente a task debe arrojar un status 400
if ($resource != "empleados") {
    // header("{$_SERVER['SERVER_PROTOCOL']} 404 Not found"); primera opcion
    http_response_code(404); // 2° forma
    exit;
}

//debemos configurar el content type del contenido de la respuesta (JSON)
header("Content-type: application/json; charset=UTF-8");
//objeto de la clase database usamos el archivo env
$database = new DataBase($_ENV["DB_HOST"], $_ENV["DB_NAME"], $_ENV["DB_USER"], $_ENV["DB_PASS"]);

//creamos objeto clase TaskGateway
$taskGateway=new TaskGateway($database);
//creamos un objeto de la clase TaskController
$controller = new TaskController($taskGateway);

//llamamos al metodo(process) de la clase y colocamos sus parametros (metodo usado,id)
$controller->processRequest($_SERVER['REQUEST_METHOD'], $id);
