<?php
class RowStatus{
    public const NEW = 1;
    public const NEWMODIFIED = 2;
    public const NOTMODIFIED = 3;
    public const MODIFIED = 4;
}

class ColStatus{
    public const NOTMODIFIED = 1;
    public const MODIFIED = 2;
}
class Entidad{

    public  $DB;
    public  $last_error;
    public  $db_name = "";

    public  $table_name = "";
    public  $SELECT = "";
    public  $FROM = "";
    public  $WHERE = "";
    public  $query = "";
    
    
    /*["col_name =>["db_name" 
                        "type" 
                        "primary" 
                        "updateable" 
                        "value" 
                        "original" 
                        "status"]] 
        */
    public $dataColumns = [];
   
    public $status = RowStatus::NEW;
    public $delete = false;
    public $retrieved = false;

   

    /**
     * "PropertyName" => ["property" => "PropertyName", 
     *					"key"=>"Entity.primary_key", 
     *					"key_rel" => "SubEntity.foreign_key", 
     *					"class_name" => "UsuarioInteres",
     *                  "method" => "métodoPara "]
     * 
     */
    public  $maps = [];

    public function __construct($DB,$data = [], $rowStatus = RowStatus::NEW){
            $this->query = $this->SELECT . $this->FROM . $this->WHERE;
            $this->DB = $DB;
            //El constructor recibe un arreglo con las columnas como llaves
            if($rowStatus == RowStatus::NEW || $rowStatus == RowStatus::MODIFIED){
                $colStatus = ColStatus::MODIFIED;
            }
            if($rowStatus == RowStatus::NOTMODIFIED){
                $colStatus = ColStatus::NOTMODIFIED;
            }
            $this->retrieved = in_array($rowStatus,
                                        [RowStatus::MODIFIED,RowStatus::NOTMODIFIED]);
            foreach($data as $key => $value){
                $this->dataColumns[$key]["value"] = $value;
                $this->dataColumns[$key]["original"] = $value;
                $this->dataColumns[$key]["status"] = $colStatus;
            }
            
            $this->status = $rowStatus;
    }

    public static function getEntidades($DB,$ClassName, $where, $params,&$error){
            $entity = new $ClassName();
            $qry = $entity->SELECT . $entity->FROM . $where;

            try{

                $stmt = $DB->prepare($qry);
                $stmt->execute($params);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            }catch(Exception $e){
    
                $error = $e->getMessage();
                return [];
    
            }
            if(!$rows){
                return null;
            }

            foreach($rows as $row){
                $entidades[] = new $ClassName($row,RowStatus::NOTMODIFIED);
            }

            return $entidades;

    }
    public function retrieve($pk){
        
        if(!$pk){

            return;
        }

        try{

            $stmt = $this->DB->prepare($this->query);
            $stmt->execute([$pk]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

        }catch(Exception $e){

            $this->last_error = $e->getMessage();
            return;

        }

        foreach($row as $key => $value){
            $this->dataColumns[$key]["value"] = $value;
            $this->dataColumns[$key]["original"] = $value;
            $this->dataColumns[$key]["status"] = ColStatus::NOTMODIFIED;
        }
        
        $this->status = RowStatus::NOTMODIFIED;
        
        
    }
    public function save(){
        if( $this->status == RowStatus::NEW || 
            $this->status == RowStatus::NOTMODIFIED){
            //Sin cambios
            return true;

        }
        if($this->delete){
            if($this->status == RowStatus::NEW ||
                $this->status == RowStatus::NEWMODIFIED){
                    return true;
                }

                //Child Rows?
                //Todo: Delete child rows
                return $this->delete();

        }
        if($this->status == RowStatus::NEWMODIFIED){
            return $this->insert();
        }

        if($this->status == RowStatus::MODIFIED){
            return $this->update();
        }

    }
    private function saveChilds(){

    }
    private function retrieveChilds(){
        
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
            $this->setPK(self::$DB->lastInsertId());

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
    private function delete(){}
    public function addChild($prop_name){
        $this->$prop_name[] = new $this->maps[$prop_name]["class_name"];
    }

    public function getChild($prop_name, $callback){
        //
        return array_filter($this->$prop_name,$callback());
    }


    public function setColumnValue($col_name, $value){
        //$this->$col_name = $value;
        $this->dataColumns[$col_name]["value"] = $value;
        $this->dataColumns[$col_name]["status"] = ColStatus::MODIFIED;
    }

    public function __set($col_name, $value){
        $this->dataColumns[$col_name]["value"] = $value;
        $this->dataColumns[$col_name]["status"] = ColStatus::MODIFIED;
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