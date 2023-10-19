<?php
//composer autoloader
require __DIR__ . "/vendor/autoload.php";
//verificamos que el metodo enviado por el formulario sea post
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    //para poder usar el archivo de configuracion global.env
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    
    //objeto de conexion a la BD
    $database = new Database($_ENV["DB_HOST"],
                             $_ENV["DB_NAME"],
                             $_ENV["DB_USER"],
                             $_ENV["DB_PASS"]);
                             
    $conn = $database->getConnection();
    
    //insertamos el nuevo usuario con los parametros a recibir
    $sql = "INSERT INTO user (name, username, password_hash, api_key)
            VALUES (:name, :username, :password_hash, :api_key)";
            
    $stmt = $conn->prepare($sql);
    //funcion para que la contraseña enviada del formulario quede encriptada
    $password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);
    //para generar la clave de registro del API  (crea un string de caracteres dificiles de 16)
    //despues bind2hex la convierte a una cadea de 32 letras y numeros
    $api_key = bin2hex(random_bytes(16));
    
    $stmt->bindValue(":name", $_POST["name"], PDO::PARAM_STR);
    $stmt->bindValue(":username", $_POST["username"], PDO::PARAM_STR);
    //vincular el parametro con la contraseña encriptada
    $stmt->bindValue(":password_hash", $password_hash, PDO::PARAM_STR);
    //vincular clave de api
    $stmt->bindValue(":api_key", $api_key, PDO::PARAM_STR);
    
    $stmt->execute();
    
    echo "Thank you for registering. Your API key is ", $api_key;
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="https://unpkg.com/@picocss/pico@latest/css/pico.min.css">
</head>
<body>
    
<!--Cuando encierras tu codigo con el container aplica los estilos a lo que este dentro-->
    <main class="container">
    
        <h1>Register</h1>
        
        <form method="post">
            
            <label for="name">
                Name
                <input name="name" id="name">
            </label>
            
            <label for="username">
                Username
                <input name="username" id="username">
            </label>
            
            <label for="password">
                Password
                <input type="password" name="password" id="password">
            </label>
            
            <button>Register</button>
        </form>
    
    </main>
    
</body>
</html>
        
        
        
        
        
        
        
        
        
        
        
        
        