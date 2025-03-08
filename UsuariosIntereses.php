<?php
class UsuariosIntereses extends Entidad{
    
    public $db_name = "crud_basico";
    public $table_name = "usuarios_intereses";

    public $SELECT = "SELECT ui.id_usuario_interes,
                             ui.id_usuario,
                             ui.id_interes,
                             i.interes\n";
    
    public $FROM = "FROM    crud_basico.usuarios_intereses ui
                            INNER JOIN crud_basico.intereses i ON
                            ui.id_interes = i.id_interes\n";
    public $WHERE = "WHERE  ui.id_usuario_interes = ?";
    public $query;

    public $dataColumns = [
            "id_usuario_interes" => [   "db_name" => "id_usuario_interes",
                                "type" => "integer", 
                                "primary" => true, 
                                "updateable" => true, 
                                "value" => null, 
                                "original" => null, 
                                "status" => PropertyStatus::NOTMODIFIED],
            "id_usuario" => [  "db_name" => "id_usuario",
                            "type" => "integer", 
                            "primary" => false, 
                            "updateable" => true, 
                            "value" => null, 
                            "original" => null, 
                            "status" => PropertyStatus::NOTMODIFIED],
            "id_interes" => [  "db_name" => "id_interes",
                            "type" => "integer", 
                            "primary" => false, 
                            "updateable" => true, 
                            "value" => null, 
                            "original" => null, 
                            "status" => PropertyStatus::NOTMODIFIED],
            "interes" => [  "db_name" => "interes",
                            "type" => "string", 
                            "primary" => false, 
                            "updateable" => false, 
                            "value" => null, 
                            "original" => null, 
                            "status" => PropertyStatus::NOTMODIFIED]
    ];
}

