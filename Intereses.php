<?php
include "./Entidad.php";
class Intereses extends Entidad{
    
    public $db_name = "crud_basico";
    public $table_name = "intereses";

    public $SELECT = "SELECT id_interes,
                             interes\n";
    
    public $FROM = "FROM crud_basico.intereses\n";
    public $WHERE = "WHERE  id_interes = ?";
    public $query;

    public $dataColumns = [
            "id_interes" => [   "db_name" => "id_interes",
                                "type" => "integer", 
                                "primary" => true, 
                                "updateable" => true, 
                                "value" => null, 
                                "original" => null, 
                                "status" => PropertyStatus::NOTMODIFIED],
            "interes" => [  "db_name" => "interes",
                            "type" => "string", 
                            "primary" => false, 
                            "updateable" => "true", 
                            "value" => null, 
                            "original" => null, 
                            "status" => PropertyStatus::NOTMODIFIED]
    ];
}

include "./db_cnx.php";

var_dump((new Intereses($cnx))->retrieve(1));

$ent = Intereses::getEntidades($cnx,"",[],$err);
echo "<pre>";
var_dump($ent);
echo "</pre>";
