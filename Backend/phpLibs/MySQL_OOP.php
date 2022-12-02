<?php
class DB{
    function __construct(String $DBHost = "127.0.0.1", String $DBUser = "root", String $DBPassword = "", String $DBName = "", int $DBPort = 3306){
        $this->conn = new mysqli($DBHost, $DBUser, $DBPassword, $DBName, $DBPort);
        if ($this->conn->connect_error) {
            die("[-]Connection failed: " . $this->conn->connect_error);
            return FALSE;
        }
        $this->updateLastID();
        if ($DBName != ""){
            $this->conn->query("USE $DBName;");
            return TRUE;
        }
        else {
            return TRUE;
        }
    }

    function creation(String $DBName){
        $sql = "CREATE DATABASE $DBName";
        if ($this->conn->query($sql) === TRUE) {
            echo "[+]Database created successfully";
            $this->conn->query("USE $DBName;");
        } 
        else {
            echo "[-]Error creating database: " . $this->conn->error;
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
        if ($this->conn->query($sql) === TRUE) {
            echo "[+]Table created successfully";
        }
        else {
            echo "[-]Error creating table: " . $this->conn->error;
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
        if ($this->conn->query($sql) === TRUE) {
            echo "[+]Data inserted successfully";
        } 
        else {
            echo "[-]Error: " . $sql . "<br>" . $this->conn->error;
        }
        $this->updateLastID();
    }

    function insertMultiple(String $TName, array $M2Insert){
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
        }
        if ($this->conn->multi_query($sql) === TRUE) {
            echo "[+] $nInsert Data inserted successfully";
        } 
        else {
            echo "[-] $nInsert Error: " . $sql . "<br>" . $this->conn->error;
        }
        $this->lastID = $this->conn->insert_id;
    }

    function getData(String $TName){
        $sql = "SELECT * FROM $TName";
        $resultat = $this->conn->query($sql);
        $data = array();
        while ($row = mysqli_fetch_assoc($resultat)) {
            array_push($data, $row);
        }
        return ($data);
    }

    function updateLastID(){
        $this->lastID = $this->conn->insert_id;
    }

    function close(){
        $this->conn->close();
    }
}

?>