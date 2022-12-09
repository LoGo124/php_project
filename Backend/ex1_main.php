<?php
    include "phpLibs/general_tools.php";
    include "phpLibs/MySQL_PDO.php";

    function main(){
        if ($MyDB = new DB("127.0.0.1","user","aplicacions","dadesAmbientals",3306)) {
            print_r($_POST);
            $data = $MyDB->getData("dades");
            if (isset($_POST["parte"])){
                printParte();
            }
            else {
                printHome();
            }
        }
        else {
            echo "<h1>[-] No s'ha pogut accedir a la base de dades.</h1>";
        }
    }

    function createDadesAmbientals(){
        if ($MyDB = new DB()) {
            if ($MyDB->creation("dadesAmbientals")==False){
                $MyDB = new DB("127.0.0.1","user","aplicacions","dadesAmbientals");
            }
            else {
                $MyDB = new DB("127.0.0.1","user","aplicacions","dadesAmbientals");
            }
            $MyDB->tableCreation("dades_mes",array("id"=>array("INT", "NOT NULL", "AUTO_INCREMENT", "PRIMARY KEY"),
                                                    "ubicacio"=>array("VARCHAR(20)"),
                                                    "datahora"=>array("DATETIME"),
                                                    "temperatura"=>array("FLOAT", "NOT NULL"),
                                                    "humitat"=>array("FLOAT", "NOT NULL")));
            $MyDB->tableCreation("dades_any",array("id"=>array("INT", "NOT NULL", "AUTO_INCREMENT", "PRIMARY KEY"),
                                                    "ubicacio"=>array("VARCHAR(20)"),
                                                    "datahora"=>array("DATETIME", "NOT NULL"),
                                                    "Tmin"=>array("FLOAT", "NOT NULL"),
                                                    "Hmin"=>array("FLOAT", "NOT NULL"),
                                                    "Tmed"=>array("FLOAT", "NOT NULL"),
                                                    "Hmed"=>array("FLOAT", "NOT NULL"),
                                                    "Tmax"=>array("FLOAT", "NOT NULL"),
                                                    "Hmax"=>array("FLOAT", "NOT NULL")));
        }
    }

    function printHome(){
        echo 
        "<form action=\"dadesClimatiques.php\" method=\"post\">
            <legend>Parts de la casa</legend>
            
            <table class=\"tg\">
                <tr>
                    <td class=\"tg-0pky\" colspan=\"3\" rowspan=\"3\"><input type=\"submit\" name=\"parte\" value=\"Habitacio 1\"></td>
                    <td class=\"tg-0pky\" rowspan=\"5\"><input type=\"submit\" name=\"parte\" value=\"Pasadis\"></td>
                    <td class=\"tg-0pky\" colspan=\"3\" rowspan=\"2\"><input type=\"submit\" name=\"parte\" value=\"Habitacio 2\"></td>
                </tr>
                <tr>
                </tr>
                <tr>
                    <td class=\"tg-0pky\" colspan=\"3\" rowspan=\"3\"><input type=\"submit\" name=\"parte\" value=\"Cuina\"></td>
                </tr>
                <tr>
                    <td class=\"tg-0pky\" colspan=\"3\" rowspan=\"2\"><input type=\"submit\" name=\"parte\" value=\"Labavo\"></td>
                </tr>
                <tr>
                </tr>
                <tr>
                    <td class=\"tg-0pky\" colspan=\"5\" rowspan=\"2\"><input type=\"submit\" name=\"parte\" value=\"Menjador\"></td>
                    <td class=\"tg-0pky\" colspan=\"2\" rowspan=\"2\"><input type=\"submit\" name=\"parte\" value=\"Labavo\"></td>
                </tr>
                <tr>
                </tr>
            </table>
        </form>";
    }

    function printParte(){
        echo "<h1>".$_POST["parte"]."</h1>";
    }

    function printTable(array $data, string $autoSelect = "" ){
        echo "<form action=\"dadesAmbientals.php\" method=\"post\"><table><tr><th>ID</th><th>Dispositiu</th><th>Data</th><th>Solicitant</th><th>Correu</th><th>Descripcio</th><th><input type=\"submit\" value=\"tot\" name=\"query\"></th></tr>";
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