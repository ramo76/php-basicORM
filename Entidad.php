<?php
class EntityStatus{
    public const NEW = 1;
    public const NEWMODIFIED = 2;
    public const NOTMODIFIED = 3;
    public const MODIFIED = 4;
    public const DELETED = 5;
}

class PropertyStatus{
    public const NOTMODIFIED = 1;
    public const MODIFIED = 2;
}
class Entidad{

    public  $DB;
    public  $last_error;
    public  $affectedRows;
    public  $db_name = "";

    public  $table_name = "";
    public  $SELECT = "";
    public  $FROM = "";
    public  $WHERE = "";
    public  $query = "";
    
    
    /**
     * Structure of dataColumn element:
     * ["col_name =>[
     *  "db_name" => <column database name>
     *  "type" => <string | integer | float | datetime>
     *  "primary" => <true | false>
     *  "updateable" => <trud | false>
     *  "value" => <data value>
     *  "original" => <original data value>
     *  "status" => <ColStatus::MODIFIED | NOTMODIFIED>
     * ]...] 
     **/
    public $dataColumns = [];
   
    public $status = EntityStatus::NEW;
    public $delete = false;
    public $retrieved = false;

   

    /**
     * Structure of map element:
     * "PropertyName" => [
     * "property" => "PropertyNameToAssing", 
     * "key"=>"Entity.primary_key", 
     * "key_rel" => "SubEntity.foreign_key",
     * "key_qry" => "The foreign key in select of ChildEntity
     * "class" => <SubEntityClassName>,
     * "where" =>"Where statement to get ChildEntities"]
     * 
     */
    public  $maps = [];

    public function __construct($DB,$data = [], $rowStatus = EntityStatus::NEW){
        
        $this->query = $this->SELECT . $this->FROM . $this->WHERE;
        $this->DB = $DB;
        //El constructor recibe un arreglo con las columnas como llaves
        if($rowStatus == EntityStatus::NEW || $rowStatus == EntityStatus::MODIFIED){
            $colStatus = PropertyStatus::MODIFIED;
        }
        if($rowStatus == EntityStatus::NOTMODIFIED){
            $colStatus = PropertyStatus::NOTMODIFIED;
        }
        $this->retrieved = in_array($rowStatus,
                                    [EntityStatus::MODIFIED,EntityStatus::NOTMODIFIED]);
        
        foreach($data as $key => $value){
            $this->dataColumns[$key]["value"] = $value;
            $this->dataColumns[$key]["original"] = $value;
            $this->dataColumns[$key]["status"] = $colStatus;
        }
        
        $this->status = $rowStatus;
            
    }

    public function setStatus($status){
        $this->status = $status;
    }

    public static function getEntidades($DB, $where, $params,&$error){
            
        $ClassName = static::class;
        $entity = new $ClassName($DB);
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
            $error = "No data returned from getEntidades: $ClassName";
            return null;
        }

        foreach($rows as $row){
            $entidades[] = new $ClassName($DB,$row,EntityStatus::NOTMODIFIED);
        }

        return $entidades;

    }
    public function retrieve($pk){
        
        if(!$pk){

            return $this;
        }

        try{

            $stmt = $this->DB->prepare($this->query);
            $stmt->execute([$pk]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

        }catch(Exception $e){

            $this->last_error = $e->getMessage();
            return $this;

        }
        if(!$row){
            $this->retrieved = false;
            return $this;
        }
        foreach($row as $key => $value){
            $this->dataColumns[$key]["value"] = $value;
            $this->dataColumns[$key]["original"] = $value;
            $this->dataColumns[$key]["status"] = PropertyStatus::NOTMODIFIED;
        }
        
        $this->status = EntityStatus::NOTMODIFIED;
        $this->retrieveChilds();
        return $this;
    }
    public function retrieveChilds(){
        
        $err = [];

        foreach($this->maps as $key => $map){

            $err[] = "";
            $this->{$map["property"]} = $map["class"]::getEntidades($this->DB,
                                            "WHERE {$map['key_qry']} = ?",
                                            [$this->{$map['key']}],
                                            $err[]);

        }
        $this->last_error = implode("\n",$err);
        return $this;
    }
    public function save(){

        if( $this->status == EntityStatus::NEW || 
            $this->status == EntityStatus::NOTMODIFIED){
            //Sin cambios
            return true;

        }
        if( $this->status == EntityStatus::DELETED){
            
                //Child Rows?
                //Todo: Delete child rows
                echo "<br> BORRANDO USUARIO $this->id_usuario <br>";
                return $this->delete();

        }
        if($this->status == EntityStatus::NEWMODIFIED){
            return $this->insert();
        }

        if($this->status == EntityStatus::MODIFIED){
            return $this->update();
        }

    }
    public function saveChilds(){
        $err = [];

        foreach($this->maps as $key => $map){

            foreach($this->{$map["property"]} as $subEntity){
                $subEntity->{$map["key_rel"]} = $this->{$map["key"]};
                var_dump($subEntity);
                if(!$subEntity->save()){
                    $err[] = $subEntity->last_error;
                }
            }

        }
        if(count($err)){
            $this->last_error = implode("\n",$err);
            return false;
        }
       
        return true;
    }
   
    public function insert(){

        $params = [];
        $qry = $this->genInsert($params);
    
        if(!$params){
            return true;
        }
        try{

            $stmt = $this->DB->prepare($qry);
            $stmt->execute($params);
            $pk = $this->DB->lastInsertId();
            $this->affectedRows = $stmt->rowCount();
            $this->setPK($pk);

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
            $this->affectedRows = $stmt->rowCount();

        }catch(Exception $e){

            $this->last_error = $e->getMessage();
            return false;

        }

        return true;
    }
    private function delete(){
        $params = [];
        $qry = $this->genDelete($params);
        if(!$params){
            return true;
        }
        try{

            $stmt = $this->DB->prepare($qry);
            $stmt->execute($params);
            $this->affectedRows = $stmt->rowCount();

        }catch(Exception $e){

            $this->last_error = $e->getMessage();
            return false;

        }

        return true;
    }
    public function addChild($prop_name){
        $subEntity = new $this->maps[$prop_name]["class"]($this->DB);
        $this->$prop_name[] = $subEntity;
        return $subEntity;
    }

    public function getChild($prop_name, $callback){
        //
        return array_filter($this->$prop_name,$callback());
    }


    public function setColumnValue($col_name, $value){
        //$this->$col_name = $value;
        $this->dataColumns[$col_name]["value"] = $value;
        $this->dataColumns[$col_name]["status"] = PropertyStatus::MODIFIED;
        $this->status = $this->status == EntityStatus::NEW ? EntityStatus::NEWMODIFIED : $this->status;
        $this->status = $this->status == EntityStatus::NOTMODIFIED ? EntityStatus::MODIFIED : $this->status;
    }

    public function __set($col_name, $value){
        $this->dataColumns[$col_name]["value"] = $value;
        $this->dataColumns[$col_name]["status"] = PropertyStatus::MODIFIED;
        $this->status = $this->status == EntityStatus::NEW ? EntityStatus::NEWMODIFIED : $this->status;
        $this->status = $this->status == EntityStatus::NOTMODIFIED ? EntityStatus::MODIFIED : $this->status;
    }

    public function __get($col_name){
    	
        return $this->dataColumns[$col_name]["value"];
        
    }

    public function genInsert(&$params){
        $insert = "INSERT INTO $this->db_name.$this->table_name(";
        //Obtener columnas a insertar, se omite la columna primary
        //ya que se asume auto_increment.
        $cols = array_reduce($this->dataColumns, 
                            function($acum,$column) use(&$params){
            
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

    public function genUpdate(&$params){
        $cols = array_reduce($this->dataColumns, 
                             function($acum,$column)use(&$params) {
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
                            function($acum,$column)use(&$params) {
            if($column["primary"]){
                $params[] = $column["value"];
                return $acum .= $column["db_name"] . " = ? AND";
            }
            return $acum;
        });

        $where = substr($where,0,-3);
        $update .= $where;
        return $update;

    }

    public function genDelete(&$params){

        $delete = "DELETE FROM $this->db_name.$this->table_name\nWHERE ";
        
        $where = array_reduce($this->dataColumns, 
                            function($acum,$column)use(&$params) {
            if($column["primary"]){
                $params[] = $column["value"];
                return $acum .= $column["db_name"] . " = ? AND";
            }
            return $acum;
        });

        $where = substr($where,0,-3);
        $delete .= $where;
        return $delete;

    }

    public function setPK($pk){
        foreach($this->dataColumns as &$column){
           
            if($column["primary"]){
                $column["value"] = $pk;
                return;
            }
        }
    }
}