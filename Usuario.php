<?php

class Usuario{

    public $DB;
    public $last_error;
    public $retrieved = false;
    /**
     * entity status: 1->new, 2->new modified, 3->not modified, 4->modified
     * dataColumns.status 1->not modified 2-> modified
     */
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
    public $query = "SELECT u.id_usuario,
                            u.nombres,
                            u.apaterno,
                            u.amaterno,
                            u.correo,
                            u.password,
                            u.id_genero,
                            u.id_plan,
                            p.plan,
                            g.genero
                    FROM    usuarios u

                            INNER JOIN generos g ON
                            u.id_genero = g.id_genero

                            INNER JOIN planes p ON
                            p.id_plan = u.id_plan
                    WHERE   u.id_usuario = ?";
    public $status = 1;
    public $delete = false;

    public $db_name = "";

    public $table_name = "";

    public $maps = [
    	"intereses" => ["property" => "intereses", 
    					"key"=>"id_usuario", 
    					"key_rel" => "id_usuario", 
    					"class_name" => "UsuarioInteres"]
    ];
    
    public $intereses = [];

    public function retrieve($pk){
        try{

            $stmt = $this->DB->prepare($this->query);
            $stmt->execute([$pk]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

        }catch(Exception $e){

            $this->last_error = $e->getMessage();
            $this->retrieved = false;
            return false;

        }
        if(!$row){

            $this->retrieved = false;
            return 0;

        }

        $this->retrieved = true;
        $this->status = 3; //Not Modified

        foreach($row as $key => $value){
            $this->dataColumns[$key]["value"] = $value;
            $this->dataColumns[$key]["original"] = $value;
        }
        return true;
    }

    private function insert(){
        $params = [];
        $qry = $this->genInsert($params);
        if(!$params){
            return true;
        }
        try{

            $stmt = $this->DB->prepare($qry);
            $stmt->execute($params);
            $this->setPK($this->DB->lastInsertId);

        }catch(Exception $e){

            $this->last_error = $e->getMessage();
            return false;

        }

        return true;
    }

    public function update()  {
        $params = [];
        $qry = $this->genUpdate($params);
        if(!$params){
            return true;
        }
        try{

            $stmt = $this->DB->prepare($qry);
            $stmt->execute($params);
            $this->num;

        }catch(Exception $e){

            $this->last_error = $e->getMessage();
            return false;

        }

        return true;
    }
    public function addChild($prop_name){
        $this->$prop_name[] = new $this->maps[$prop_name]["class_name"];
    }

    public function getChild($prop_name, $callback){
        //
    }


    public function setColumnValue($col_name, $value){
        //$this->$col_name = $value;
        $this->dataColumns[$col_name]["value"] = $value;
        $this->dataColumns[$col_name]["status"] = 2;
    }

    public function __set($col_name, $value){
        $this->dataColumns[$col_name]["value"] = $value;
        $this->dataColumns[$col_name]["status"] = 2;
    }

    public function __get($col_name){
    	
        return $this->dataColumns[$col_name]["value"];
        
    }

    private function genInsert($params){
        $insert = "INSERT INTO $this->db_name.$this->table_name(";
        //Obtener columnas a insertar, se omite la columna primary
        //ya que se asume auto_increment.
        $cols = array_reduce($this->dataColumns, 
                            function($acum,$column) use($params){
            if($column["updateable"] && $column["status"] == 2 && !$column["primary"]){
                $params[] = $column["value"];
                return $acum .= $column["db_name"] . ",";
            }
            return $acum;
        });

        if(!$params){
            return "";
        }
        //Quitar la última coma
        $cols = substr($cols,0,-1);
        //Formar la cadena con los parámetros
        $placeholders = substr(str_repeat("?,",
                                count($params)),
                                0,-1);
        //retornar el string con la sentencia insert
        return $insert .= $cols .") VALUES(" . $placeholders .")";
    }

    private function genUpdate($params){
        $cols = array_reduce($this->dataColumns, 
                            function($acum,$column)use($params) {
            if($column["updateable"] && $column["status"] == 2 && !$column["primary"]){
                $params[] = $column["value"];
                return $acum .= $column["db_name"] . " = ? ,";
            }
            return $acum;
        });

        if(!$params){
            return "";
        }

        $cols = substr($cols,0,-1);

        $update = "UPDATE $this->db_name.$this->table_name SET ";
        $update .= $cols;
        $update .= " WHERE ";

        $where = array_reduce($this->dataColumns, 
                            function($acum,$column)use($params) {
            if($column["primary"]){
                $params[] = $column["value"];
                return $acum .= $column["db_name"] . " = ? AND";
            }
            return $acum;
        });

        $where = substr($cols,0,-3);
        $update .= $where;
        return $update;

    }

    private function setPK($pk){
        foreach($this->dataColumns as $column){
            if($column["primary"]){
                $column["value"] = $pk;
                return;
            }
        }
    }
}