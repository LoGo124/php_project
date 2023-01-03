<?php
    include "phpLibs/general_tools.php";
    include "phpLibs/MySQL_PDO.php";


    /**
     * 
     */
    function main(){
        $loged = checkLog();
        // DEBUG CONTROL
            //POST Contiene los datos de a que intentamos acceder
        #print_r($_POST);
        #    //COOKIE Contiene los datos de la ultima conexión tmb login y passwd, si hace mas de 3h o no hay, se conecta a la base de datos para actualizar los datos
        #print_r($_COOKIE);
        #    //SESSION Contiene los datos del usuario
        #print_r($_SESSION);
        #print_r(session_id());
        
        if ($loged && isset($_POST["parte"])) {
            printHome($_POST["parte"]);
            if ($MyDB = new DB("127.0.0.1","user","aplicacions","dadesAmbientals",3306)) {
                $rawYData = $MyDB->getData("dades_any","ubicacio='".$_POST["parte"]."'");
                $rawMData = $MyDB->getData("dades_mes","ubicacio='".$_POST["parte"]."'");
                
                $YData = procesSetY($rawYData);
                $MData = procesSetM($rawMData);

                printDatos($YData, $MData, $_POST["parte"]);
            }
            else {
                echo "<h1>[-] No s'ha pogut accedir a la base de dades.</h1>";
            }
        }
        elseif ($loged) {
            printHome();
            if ($MyDB = new DB("127.0.0.1","user","aplicacions","dadesAmbientals",3306)) {
                # DATOS GENERALES
                $rawYData = $MyDB->getData("dades_any");
                $rawMData = $MyDB->getData("dades_mes");
                
                $YData = procesSetY($rawYData);
                $MData = procesSetM($rawMData);

                printDatos($YData, $MData);
            }
            else {
                echo "<h1>[-] No s'ha pogut accedir a la base de dades.</h1>";
            }
        }
    }

    # Funciones para generar html
    /**
     * Dona la benvinguda als usuaris que no han iniciat sesió o ha caducat hi han de tornar a posar la contrasenya.
     */
    function printNav(){
        # code...
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

    function printDatos($YData, $MData, $section = "general"){
        echo "<div id=\"$section\"><h2>".$section."</h2>";
        printTable($YData, "Dades de l'any");
        printTable($MData, "Dades del mes");
        echo"</div>";
    }

    function printLoginPage($userName = ""){
        echo "
        <head>
            <title>Inicio de sesión</title>
        </head>
        <body>
            <h1>Benvingut al sistema de dades climatiques de la casa.</h1>
            <p>Por favor, ingrese su nombre de usuario y contraseña para acceder a su cuenta.</p>
            <form method=\"post\" action=\"dadesClimatiques.php\" id=\"login\">
                <label for=\"username\">Nombre de usuario:</label>
                <input type=\"text\" id=\"username\" name=\"username\" value=\"$userName\"><br>
                <label for=\"password\">Contraseña:</label>
                <input type=\"password\" id=\"password\" name=\"password\"><br><br>
                <input type=\"submit\" value=\"Iniciar sesión\">
            </form> 
        </body>";
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

    #Security
    function checkLog(){
        session_start();
        if (isset($_POST["username"])) {
            saveOnSession(array("username" => $_POST["username"],"session_id" => $_POST["password"]));
            setcookie("username", $_POST["username"], time() + (60 * 3));
            setcookie("session_id", session_id(), time() + (60 * 3));
            return (true);
        }
        elseif (isset($_SESSION) && isset($_COOKIE["session_id"]) && $_COOKIE["session_id"] == session_id()) {
            return (true);
        }
        elseif (isset($_SESSION["username"])) {
            printError("Sessio caducada");
            printLoginPage($_SESSION["username"]);
            return (false);
        }
        elseif (isset($_COOKIE["username"])) {
            printLoginPage($_COOKIE["username"]);
            return (false);
        }
        else {
            printLoginPage();
            return (false);
        }

    }
    
    #Procesado de datos
    function procesSetM(array $dataM){
        $cleanData = array("header" => array("", "MIN", "MED", "MAX"), "temp" => array("rowTitle" => "T", "min" => 101, "med" => 0, "max" => 0), "hum" => array("rowTitle" => "H", "min" => 101, "med" => 0, "max" => 0));
        $count = 0;
        foreach ($dataM as $id => $reg) {
            $temp[] = $reg["temperatura"];
            $hum[] = $reg["humitat"];
            $cleanData["temp"]["med"] += $reg["temperatura"];
            $cleanData["hum"]["med"] += $reg["humitat"];
            $count++;
        }
        $cleanData["temp"]["min"] = min($temp);
        $cleanData["temp"]["max"] = max($temp);
        $cleanData["hum"]["min"] = min($hum);
        $cleanData["hum"]["max"] = max($hum);
        $cleanData["temp"]["med"] = round(($cleanData["temp"]["med"] / $count), 2);
        $cleanData["hum"]["med"] = round(($cleanData["hum"]["med"] / $count), 2);
        return($cleanData);
    }

    function procesSetY(array $dataY){
        $cleanData = array("header" => array("", "MIN", "MED", "MAX"), "temp" => array("rowTitle" => "T", "min" => 101, "med" => 0, "max" => 0), "hum" => array("rowTitle" => "H", "min" => 101, "med" => 0, "max" => 0));
        $count = 0;
        foreach ($dataY as $id => $reg) {
            if ($cleanData["temp"]["min"] > $reg["Tmin"] ) {
                $cleanData["temp"]["min"] = $reg["Tmin"];
            }
            if ($cleanData["hum"]["min"] > $reg["Tmin"] ) {
                $cleanData["hum"]["min"] = $reg["Hmin"];
            }
            if ($cleanData["temp"]["max"] < $reg["Tmax"] ) {
                $cleanData["temp"]["max"] = $reg["Tmax"];
            }
            if ($cleanData["hum"]["max"] < $reg["Hmax"] ) {
                $cleanData["hum"]["max"] = $reg["Hmax"];
            }
            $cleanData["temp"]["med"] += $reg["Tmed"];
            $cleanData["hum"]["med"] += $reg["Hmed"];
            $count++;
        }
        $cleanData["temp"]["med"] = round(($cleanData["temp"]["med"] / $count), 2);
        $cleanData["hum"]["med"] = round(($cleanData["hum"]["med"] / $count), 2);
        return($cleanData);
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
            if (/*False */ True ) {
                genRegs();
            }                         
        }
    }

    function genRegs(){
        $habs = array("Labavo", "Habitacio 1", "Habitacio 2", "Cuina", "Menjador", "Pasadis", "Servidors");
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
        foreach ($days as $dia) {
            foreach ($habs as $hab) {
                $tempMin = rand(10, 20);
                    $humMin = rand(10, 30);
                    $tempMed = rand(20, 30);
                    $humMed = rand(30, 50);
                    $tempMax = rand(30, 40);
                    $humMax = rand(50, 80);
                    $MyDB->insert("dades_any", $toInsert = array("ubicacio" => $hab, "datahora" => $dia." 00:00:00", "Tmin" => $tempMin, "Hmin" => $humMin, "Tmed" => $tempMed, "Hmed" => $humMed, "Tmax" => $tempMax, "Hmax" => $humMax));
            }
        }

    }
?>