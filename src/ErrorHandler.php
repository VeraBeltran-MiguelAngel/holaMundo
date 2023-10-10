<?php
class ErrorHandler
{
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
