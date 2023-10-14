<?php
class ErrorHandler
{

    /**
     * Metodo para mostrar errores genericos como JSON
     * @param errno  numero de error
     * @param errstr mensaje de error
     * @param errfile archivo donde esta el error
     * @param errline linea del error
     */
    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): void
    {
        //mensaje.codigo de error
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
    /**
     * metodo para convertir la respuesta de error en JSON
     * @param exception usa la clase Throwable que maneja errores en PHP ver doc para entender metodos
     */
    public static function handlerException(Throwable $exception): void
    {
        http_response_code(500);

        echo json_encode([
            "code" => $exception->getCode(), //codigo de error
            "message" => $exception->getMessage(), //mensaje de error
            "file" => $exception->getFile(), //que archivo tiene el error
            "line" => $exception->getLine(), //linea del error
        ]);
    }
}
