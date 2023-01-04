<?php
class DB{
    function __construct(String $DBHost = "127.0.0.1", String $DBUser = "root", String $DBPassword = "", String $DBName = "", int $DBPort = 3306){
        try {
            $this->conn = new PDO("mysql:host=$DBHost;dbname=$DBName", $DBUser, $DBPassword);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if ($DBName != ""){
                $this->conn->query("USE $DBName;");
            }
            #echo "<p class=\"goodLog\">[+]Connected successfully</p>";
            return TRUE;
        }
        catch(PDOException $e){
            echo "<p class=\"badLog\">[-]Connection failed: " . $e->getMessage()."</p>";
            return FALSE;
        }
    }

    function creation(String $DBName){
        try {
            $this->conn->exec("CREATE DATABASE $DBName");
            echo "<p class=\"goodLog\">[+]Database created successfully<br></p>";
            return true;
        }
        catch(PDOException $e){
            echo "<p class=\"badLog\">[-]" . $e->getMessage()."</p>";
            return false;
        }

    }

    function tableCreation(String $TName, array $fields){
        $sql = "CREATE TABLE $TName (";
        foreach ($fields as $fieldName => $atributes) {
            $fields[$fieldName] = "$fieldName ";
            $fields[$fieldName] .= implode(" ",$atributes);
        }
        $sql .= implode(", ", $fields);
        $sql .= ")";
            
        try {
        $this->conn->exec($sql);
        echo "<p class=\"goodLog\">[+]Table $TName created successfully</p>";
        }
        catch(PDOException $e) {
        echo "<p class=\"badLog\">[-]" . $e->getMessage()."</p>";
        }

    }

    function insert(String $TName, array $toInsert){
        $sql = "INSERT INTO $TName (".implode(", ",array_keys($toInsert)).") VALUES (";
        foreach ($toInsert as $field => $content) {
            if (gettype($content) == "string") {
                $toInsert[$field] = "'".$content."'";
            }
        }
        $sql .= implode(", ",$toInsert).")";
        try {
            print_r($sql);
            $this->conn->exec($sql);
            echo "<p class=\"goodLog\">[+] New record created successfully</p>";
        }
        catch(PDOException $e) {
            echo "<p class=\"badLog\">[-]" . $e->getMessage()."</p>";
        }

    }

    function insertMultiple(String $TName, array $M2Insert){
        try {
            $this->conn->beginTransaction();
            $sql = "";
            foreach ($M2Insert as $nInsert => $toInsert) {
                $sql .= "INSERT INTO $TName (".implode(", ",array_keys($toInsert)).") VALUES (";
                foreach ($toInsert as $field => $content) {
                    if (gettype($content) == "String") {
                        $toInsert[$field] = "'".$content."'";
                    }
                }
                $sql .= implode(", ",$toInsert).")";
                $sql .= ";\n";
                $this->conn->exec($sql);
            }
            $this->conn->commit();
            echo "<p class=\"goodLog\">[+]New records created successfully</p>";
        } 
        catch(PDOException $e){
            $this->conn->rollback();
            echo "<p class=\"badLog\">[-]Error: " . $e->getMessage()."</p>";
        }
    }

    function delReg(String $TName,int $id){
        try {
            $sql = "DELETE FROM $TName WHERE id=$id";
            $this->conn->exec($sql);
            echo "<p class=\"goodLog\">[+]Record deleted successfully</p>";
        }
        catch(PDOException $e) {
            echo "<p class=\"badLog\">[-]" . $e->getMessage()."</p>";
        }
    }
            
    function getData(String $TName, string $condition = "", $orderField = "", string $order = "ASC", string $limit = ""){
        $sql = "SELECT * FROM $TName ";
        if ($condition) {
            $sql .= "WHERE $condition";
        }
        if ($orderField) {
            $sql .= " ORDER BY $orderField $order";
        }
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
          
            $data = array();
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
                $data[$value["id"]] = $value;
            }
            return ($data);
        }
        catch(PDOException $e) {
            echo "<p class=\"badLog\">[-]Error: " . $e->getMessage()."</p>";
        }
    }

    function updateReg(String $TName, int $id, array $mods){
        $sql = "UPDATE $TName SET ";
        $unformated = array();
        foreach ($mods as $field => $value) {
            if (gettype($value) == "string"){
                array_push($unformated,"$field=\"$value\"" );
            }
            else {
                array_push($unformated,"$field=$value" );
            }
        }
        $sql .= implode(", ", $unformated). " WHERE id=$id";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            echo "<p class=\"goodLog\">[+]".$stmt->rowCount() . " registres han sigut ACTUALITZATS</p>";
          }
        catch(PDOException $e) {
            echo "<p class=\"badLog\">[-]" . $e->getMessage()."</p>";
          }
    }

    function updateGetLastID(string $TName){
        try {
            if ($data = $this->getData($TName)){
                $this->lastID = max(array_keys($data));
                return $this->lastID;
            }
            else {
                return -1;
            }
        }
        catch(PDOException) {
            return FALSE;
        }
    }

    function close(){
        $this->conn->close();
    }
}



?>