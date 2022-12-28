<?php
    include "phpLibs/general_tools.php";
    include "phpLibs/MySQL_PDO.php";


    /**
     * 
     */
    function main(){
        #// DEBUG CONTROL
        #    //POST Contiene los datos de a que intentamos acceder
        #print_r("\nPOST = ".$_POST);
        #    //COOKIE Contiene los datos de la ultima conexión tmb login y passwd, si hace mas de 3h o no hay, se conecta a la base de datos para actualizar los datos
        #setcookie("galetes_GNJ",);
        #print_r("\nCOOKIE = ".$_COOKIE);
        #    //SESSION Contiene los datos del usuario
        #print_r("\nSESSION = ".$_SESSION);
        #if (isset($_COOKIE)) {
        #    print_r("\n TRUE");
        #}

        printHome();
        if ($MyDB = new DB("127.0.0.1","user","aplicacions","dadesAmbientals",3306)) {
            $YData = $MyDB->getData("dades_any");
            $MData = $MyDB->getData("dades_mes");
            printDatos();
        }
        else {
            echo "<h1>[-] No s'ha pogut accedir a la base de dades.</h1>";
        }
    }

    /**
     * Dona la benvinguda als usuaris que no han iniciat sesió o ha caducat hi han de tornar a posar la contrasenya.
     */
    function welcomePage($userName = ""){
        echo "<h1>Benvingut al sistema de dades climatiques de la casa.</h1>";
        echo "<form action=\"dadesClimatiques.php\" method=\"post\">
        <input type=\"text\" name=\"login\" id=\"login\" value=\"$userName\">
        <input type=\"password\" name=\"passwd\" id=\"passwd\">
        <input type=\"submit\" value=\"Login\">
        </form>";
        #<input type="checkbox" name="" id="">
    }

    function printHome($section = "general"){
        $sectons = array("general", "Labavo", "Habitacio 1", "Habitacio 2", "Cuina", "Menjador", "Pasadis", "Servidors");
        
        $sectionsClasses = array();

        $general = FALSE;
        if ($section == "general") {
            $general = TRUE;
        }

        foreach ($sectons as $value) {
            if ($value == $section || $general) {
                $sectionsClasses[$value] = "selectedClass";
            }
            else {
                $sectionsClasses[$value] = "tg-0pky";
            }
        }
        echo 
        "<form action=\"dadesClimatiques.php\" method=\"post\">
            <legend>Parts de la casa</legend>
            
            <table class=\"tg\">
                <tr>
                    <td class=\"".$sectionsClasses["Habitacio 1"]."\" colspan=\"3\" rowspan=\"3\"><input type=\"submit\" name=\"parte\" value=\"Habitacio 1\"></td>
                    <td class=\"".$sectionsClasses["Pasadis"]."\" rowspan=\"5\"><input type=\"submit\" name=\"parte\" value=\"Pasadis\"></td>
                    <td class=\"".$sectionsClasses["Habitacio 2"]."\" colspan=\"3\" rowspan=\"2\"><input type=\"submit\" name=\"parte\" value=\"Habitacio 2\"></td>
                </tr>
                <tr>
                </tr>
                <tr>
                    <td class=\"".$sectionsClasses["Cuina"]."\" colspan=\"3\" rowspan=\"3\"><input type=\"submit\" name=\"parte\" value=\"Cuina\"></td>
                </tr>
                <tr>
                    <td class=\"".$sectionsClasses["Labavo"]."\" colspan=\"3\" rowspan=\"2\"><input type=\"submit\" name=\"parte\" value=\"Labavo\"></td>
                </tr>
                <tr>
                </tr>
                <tr>
                    <td class=\"".$sectionsClasses["Menjador"]."\" colspan=\"5\" rowspan=\"2\"><input type=\"submit\" name=\"parte\" value=\"Menjador\"></td>
                    <td class=\"".$sectionsClasses["Servidors"]."\" colspan=\"2\" rowspan=\"2\"><input type=\"submit\" name=\"parte\" value=\"Servidors\"></td>
                </tr>
                <tr>
                </tr>
            </table>
        </form>";
    }

    function printDatos($section = "general"){
        echo "<div>".$_POST["parte"]."</div>";
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
    
    # Dev Tools (Preparación de la base de datos)

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
            if (False /* True */) {
                genRegs();
            }                         
        }
    }

    function genRegs(){
        $habs = array("Lavabo", "Habitacio 1", "Habitacio 2", "Cuina", "Menjador", "Pasadis", "Servidors");
        $horas = array("00:00:00", "08:00:00", "16:00:00");
        $diasmes = array("2023-01-01", "2023-01-02", "2023-01-03", "2023-01-04", "2023-01-05", "2023-01-06", "2023-01-07", "2023-01-08", "2023-01-09");
        
        $days = array();
        $year = 2022;

        for ($month = 1; $month <= 12; $month++) {
            for ($day = 1; $day <= 31; $day++) {
                // Crea una fecha en formato aaaa-mm-dd
                $date = date('Y-m-d', strtotime("{$year}-{$month}-{$day}"));
            
                // Comprueba si la fecha es válida
                if (checkdate($month, $day, $year)) {
                    $days[] = $date;
                }
            }
        }

        $MyDB = new DB("127.0.0.1","user","aplicacions","dadesAmbientals");
        // Genera datos del mes
        foreach ($diasmes as $dia) {
            foreach ($horas as $hora) {
                foreach ($habs as $hab) {
                    $temp = rand(10, 40);
                    $hum = rand(10, 80);
                    $MyDB->insert("dades_mes", $yoInsert = array("ubicacio" => $hab, "datahora" => $dia." ".$hora, "temperatura" => $temp, "humitat" => $hum));
                }
            }
        }
        // Genera datos del año
        $hora = "00:00:00";
        foreach ($days as $dia) {
            foreach ($habs as $hab) {
                $temp = rand(10, 40);
                $hum = rand(10, 80);
                $MyDB->insert("dades_mes", $yoInsert = array("ubicacio" => $hab, "datahora" => $dia." ".$hora, "temperatura" => $temp, "humitat" => $hum));
            }
        }

    }
?>