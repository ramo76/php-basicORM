<?php
$msg = "";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,	
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => true
];

try{
$cnx = new PDO('mysql:host=lamp_db;dbname=crud_basico;charset=utf8mb4',
                "web_app", 
                "010101",
                $options);
}catch(Exception $e){
    $msg = $e->getMessage();
}

if(isset($_GET["test"])){
    echo !$msg ? "Conexión exitosa" : "<h1>Error:</h1> <br> $msg";
}



