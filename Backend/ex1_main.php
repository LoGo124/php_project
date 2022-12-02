<?php
    include "phpLibs/general_tools.php";
    include "phpLibs/MySQL_PDO.php";
    
    function main(){
        if ($MyDB = new DB("127.0.0.1","user","aplicacions","Incidencies",3306)) {
            $data = $MyDB->getData("incidencies");
            if (isset($_POST["submit"])){
                $data = $MyDB->getData("incidencies");
                if (checKey($_POST["id"],$data)){
                    $MyDB->updateReg("incidencies",$_POST["id"],array("dispositiu"=>$_POST["disp"],
                                                                    "solicitant"=>$_POST["soli"],
                                                                    "correu"=>$_POST["cSoli"]));
                    printForm(array("id"=>$MyDB->updateGetLastID() +1));
                }
                elseif(buscarRepeticio($data)) {
                    printError("repe");
                }
                else{
                    $MyDB->insert("Incidencies",array("dispositiu"=>$_POST["disp"],
                                                    "data"=>$_POST["data"],
                                                    "solicitant"=>$_POST["soli"],
                                                    "correu"=>$_POST["cSoli"],
                                                    "descripcio"=>$_POST["desc"]));
                    printForm(array("id"=>$MyDB->updateGetLastID() +1));
                }
            }
            elseif (isset($_POST["query"])) {
                if ($_POST["query"] == "tot") {
                    printTable($data,"checked");
                }
                else {
                    printTable($data);
                }
            }
            elseif (isset($_POST["mod"])) {
                $formValues = array("id"=>$_POST["mod"],"disp"=>$data[$_POST["mod"]]["dispositiu"],"data"=>$data[$_POST["mod"]]["data"],"soli"=>$data[$_POST["mod"]]["solicitant"],"cSoli"=>$data[$_POST["mod"]]["correu"],"desc"=>$data[$_POST["mod"]]["descripcio"]);
                $formAtributes = array("id"=>"readonly","data"=>"readonly","desc"=>"readonly");
                printForm($formValues, $formAtributes);
            }
            elseif (isset($_POST["del"])) {
                foreach (array_keys($_POST) as $key) {
                    if ($key != "del") {
                        $MyDB->delReg("Incidencies",$key);
                    }
                }
                $data = $MyDB->getData("incidencies");
                printTable($data);
            }
            else {
                printForm(array("id"=>$MyDB->updateGetLastID()+1));
            }
        }
        else {
            echo "<h1>[-] No s'ha pogut accedir a la base de dades.</h1>";
        }
    }

    function createIncidencies(){
        if ($MyDB = new DB()) {
            if ($MyDB->creation("Incidencies")==False){
                $MyDB = new DB("127.0.0.1","user","aplicacions","Incidencies");
            }
            else {
                $MyDB = new DB("127.0.0.1","user","aplicacions","Incidencies");
            }
            $MyDB->tableCreation("Incidencies",array("id"=>array("INT", "NOT NULL", "AUTO_INCREMENT", "PRIMARY KEY"),
                                                    "dispositiu"=>array("VARCHAR(30)"),
                                                    "data"=>array("DATE"),
                                                    "solicitant"=>array("VARCHAR(50)", "NOT NULL"),
                                                    "correu"=>array("VARCHAR(50)"),
                                                    "descripcio"=>array("VARCHAR(255)")));
        }
    }

    function printForm(array $values = array(), array $atributes = array("id"=>"readonly","data"=>"required","soli"=>"","cSoli"=>"","desc"=>"")){
        $options = array("Ordenador/CPU","Impresora","Monitor","Portátil","Escaner","Teclado","Servidor","Router","Switch");
        $formatedOptions = "";
        foreach ($options as $value) {
            if ($value == checKey("disp", $values,TRUE)) {
                $formatedOptions .= "<option value=\"$value\" selected>$value</option>\n";
            }
            else {
                $formatedOptions .= "<option value=\"$value\">$value</option>\n";
            }
        }
        echo 
        "<form action=\"Incidencies.php\" method=\"post\">
            <legend>Formulari d'incidències</legend>
            <label for=\"id\">ID: </label>
            <input type=\"number\" name=\"id\" id=\"id\" value=\"".checKey("id", $values, TRUE)."\" ".checKey("id", $atributes, TRUE)."><br>
            <label for=\"disp\">Dispositiu: </label>
            <select name=\"disp\" id=\"disp\" >$formatedOptions</select><br>
            <label for=\"data\" >Data: </label>
            <input type=\"date\" name=\"data\" id=\"data\" value=\"".checKey("data", $values, TRUE)."\" ".checKey("data", $atributes, TRUE)."><br>
            <label for=\"soli\">Sol·licitant: </label>
            <input type=\"text\" name=\"soli\" id=\"soli\" value=\"".checKey("soli", $values, TRUE)."\" ".checKey("soli", $atributes, TRUE)." ><br>
            <label for=\"cSoli\">Correu Sol·licitant: </label>
            <input type=\"email\" name=\"cSoli\" id=\"cSoli\" value=\"".checKey("cSoli", $values, TRUE)."\" ".checKey("cSoli", $atributes, TRUE)."><br>
            <label for=\"desc\">Descripció de l'incidència: </label>
            <input type=\"text\" name=\"desc\" id=\"desc\" value=\"".checKey("desc", $values, TRUE)."\" ".checKey("desc", $atributes, TRUE)."><br>
            <input type=\"submit\" value=\"Envia\" id=\"submit\" name=\"submit\">
            <input type=\"submit\" value=\"Consulta\" id=\"query\" name=\"query\">
        </form>";
    }

    function printTable(array $data, string $autoSelect = "" ){
        echo "<form action=\"Incidencies.php\" method=\"post\"><table><tr><th>ID</th><th>Dispositiu</th><th>Data</th><th>Solicitant</th><th>Correu</th><th>Descripcio</th><th><input type=\"submit\" value=\"tot\" name=\"query\"></th></tr>";
        foreach ($data as $row) {
            echo "<tr>";
            foreach ($row as $key => $value) {
                if ($key == "id") {
                    echo "<td><input type=\"submit\" value=\"$value\" name=\"mod\"></td>";
                }
                else {
                    echo "<td>$value</td>";
                }
            }
            echo "<td><input type=\"checkbox\" name=\"".$row["id"]."\" $autoSelect></td></tr>";
        }
        echo "</table><input type=\"submit\" value=\"<---\" name=\"<---\"><input type=\"submit\" value=\"Esborrar\" name=\"del\"></form>";
        
    }

    function buscarRepeticio(array $data){
        foreach ($data as $id => $values) {
            if (checkValue($_POST["disp"], $values) && checkValue($_POST["data"], $values) && checkValue($_POST["soli"], $values)) {
                return true;
            }
            else {
                return false;
            }
        }
    }

    function printError(string $var = "Unknown"){
        if ($var == "repe") {
            echo "<h1> ESTA REPETIDO </h1>";
        }
        elseif ($var == "Unknown") {
            echo "<h1> ERROR DESCONEGUT </h1>";
        }
        else {
            echo "<h1> ERROR $var </h1>";
        }
    }
?>