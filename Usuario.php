<?php
class Usuario extends Entidad{
    public $dataColumns = [
        "id_usuario" => [   "db_name" => "id_usuario",
                            "type" => "integer",
                            "primary" => true,
                            "updateable" => true,
                            "value" => null,
                            "original" => null,
                            "status" => 1],

        "nombres" => [  "db_name" => "nombres",
                        "type" => "string",
                        "primary" => false,
                        "updateable" => true,
                        "value" => null,
                        "original" => null,
                        "status" => 1],
        
        "apaterno" => [  "db_name" => "apaterno",
                        "type" => "string",
                        "primary" => false,
                        "updateable" => true,
                        "value" => null,
                        "original" => null,
                        "status" => 1],

        "amaterno" => [  "db_name" => "amaterno",
                        "type" => "string",
                        "primary" => false,
                        "updateable" => true,
                        "value" => null,
                        "original" => null,
                        "status" => 1],

        "correo" => [  "db_name" => "correo",
                        "type" => "string",
                        "primary" => false,
                        "updateable" => true,
                        "value" => null,
                        "original" => null,
                        "status" => 1],

        "password" => [  "db_name" => "password",
                        "type" => "string",
                        "primary" => false,
                        "updateable" => true,
                        "value" => null,
                        "original" => null,
                        "status" => 1],
        "id_plan" => [  "db_name" => "id_plan",
                        "type" => "integer",
                        "primary" => false,
                        "updateable" => true,
                        "value" => null,
                        "original" => null,
                        "status" => 1],

        "id_genero" => [  "db_name" => "id_genero",
                        "type" => "integer",
                        "primary" => false,
                        "updateable" => true,
                        "value" => null,
                        "original" => null,
                        "status" => 1],

        "plan" => [  "db_name" => "plan",
                        "type" => "string",
                        "primary" => false,
                        "updateable" => false,
                        "value" => null,
                        "original" => null,
                        "status" => 1],

        "genero" => [  "db_name" => "genero",
                        "type" => "string",
                        "primary" => false,
                        "updateable" => false,
                        "value" => null,
                        "original" => null,
                        "status" => 1]
        
    ];

    public $SELECT = "SELECT u.id_usuario,
                            u.nombres,
                            u.apaterno,
                            u.amaterno,
                            u.correo,
                            u.password,
                            u.id_genero,
                            u.id_plan,
                            p.plan,
                            g.genero\n";
    public $FROM = "FROM    crud_basico.usuarios u

                            INNER JOIN crud_basico.generos g ON
                            u.id_genero = g.id_genero

                            INNER JOIN crud_basico.planes p ON
                            p.id_plan = u.id_plan\n";

    public $WHERE = "WHERE   u.id_usuario = ?";

    

    public $db_name = "crud_basico";

    public $table_name = "usuarios";

    public $maps = [
    	"intereses" => ["property" => "intereses", 
    					"key"=>"id_usuario", 
    					"key_rel" => "id_usuario",
                        "key_qry" => "ui.id_usuario",
    					"class" => "UsuariosIntereses"]
    ];
    
    public $intereses = [];

    
}