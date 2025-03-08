<body style="background-color:black;color:lightgrey">
<?php
include "../db_cnx.php";
include "../Entidad.php";
include "../UsuariosIntereses.php";
include "../Usuario.php";

//Create new User;
$user = new Usuario($cnx);
$user->nombres = "Jhon";
$user->apaterno = "Doe";
$user->correo = "abc@cdf.com";
$user->password = "123456";
$user->id_plan = 1;
$user->id_genero = 1;
var_dump($user->status);
echo "<pre>".print_r($user->dataColumns)."</pre>";
$params = [];
echo $user->genInsert($params);

if(!$user->save()){
    echo "Error at saving user: {$user->last_error}";
}else{
    echo "New user created:{$user->id_usuario}" . $user->dataColumns["id_usuario"]["value"];
}

echo "<h3>Actualizar Usuario</h3>";
$user2 = (new Usuario($cnx))->retrieve(5);



$user2->correo = "correoactualizado@gmail.com";
$arr = [];
var_dump($user2->genUpdate($arr));
var_dump($arr);
if(!$user2->save()){
    echo "Error at saving user: {$user->last_error}";
}else{
    echo "User updated:{$user2->id_usuario}";
}

$user3 = (new Usuario($cnx));
$user4 = (new Usuario($cnx));
$user5 = (new Usuario($cnx));

$user3->setStatus(EntityStatus::DELETED);
$user4->setStatus(EntityStatus::DELETED);
$user5->setStatus(EntityStatus::DELETED);

$arr = [];
var_dump($user3->genDelete($arr));
var_dump($arr);

$user3->save();
echo "<h1>$user3->last_error</h1>";
$user4->save();
$user5->save();
echo "<br>Usuario Eliminado: $user3->id_usuario Filas afectadas $user3->affectedRows";
echo "<br>Usuario Eliminado: $user4->id_usuario Filas afectadas $user4->affectedRows";
echo "<br>Usuario Eliminado: $user5->id_usuario Filas afectadas $user5->affectedRows";
echo "</body>";