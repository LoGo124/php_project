<?php
    include "phpLibs/general_tools.php";
    include "phpLibs/MySQL_PDO.php";

    /**
     * 
     */
    function main(){
        printAnimatedBackground();
        //echo "<link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css\" rel=\"stylesheet\" integrity=\"sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD\" crossorigin=\"anonymous\">";
        //echo "<script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js\" integrity=\"sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN\" crossorigin=\"anonymous\"></script>";
        $loged = checkLog();
        // DEBUG CONTROL
            //POST Contiene los datos de a que intentamos acceder
        #print_r($_POST);
            //COOKIE Contiene los datos de la ultima conexión tmb login y passwd, si hace mas de 3h o no hay, se conecta a la base de datos para actualizar los datos
        #print_r($_COOKIE);
            //SESSION Contiene los datos del usuario
        #print_r($_SESSION);
        #print_r(session_id());
        printNav($loged);
        if ($loged) {
            if ($MyDB = new DB("127.0.0.1","user","aplicacions","dadesAmbientals",3306)) {
                if (isset($_POST["parte"])) {
                    printHome($_POST["parte"]);
                    $ubi = "ubicacio='".$_POST["parte"]."'";
                }
                else {
                    printHome();
                    $ubi = "ubicacio = ubicacio";
                }
                $rawLData = $MyDB->getData("dades_mes",$ubi, "datahora", "DESC", "1");
                $rawYData = $MyDB->getData("dades_any",$ubi);
                $rawMData = $MyDB->getData("dades_mes",$ubi);
                $rawDData = $MyDB->getData("dades_mes",$ubi." AND `datahora` LIKE '%".date("Y-m-d")."%'");
                
                $LData = procesSetL($rawLData);
                $YData = procesSetY($rawYData);
                $MData = procesSetM($rawMData);
                $DData = procesSetD($rawDData);

                printDatos($LData, $YData, $MData, $DData);
            }
            else {
                echo "<h1>[-] No s'ha pogut accedir a la base de dades.</h1>";
            }
        }
        elseif (isset($_SESSION["username"])) {
            printLoginPage($_SESSION["username"]);
        }
        elseif (isset($_COOKIE["username"])) {
            printLoginPage($_COOKIE["username"]);
        }
        else {
            printLoginPage();
        }
    }

    # Funciones para generar html
    function printAnimatedBackground(){
        echo "<div id=\"particles-js\"></div><script src=\"../Backend/js/particles.min.js\"></script><script src=\"../Backend/js/part.js\"></script>";
    }

    /**
     * Dona la benvinguda als usuaris que no han iniciat sesió o ha caducat hi han de tornar a posar la contrasenya.
     */
    function printNav($loged){
        echo "<header> <a href=\"dadesClimatiques.php\">Sweat Smart Home</a> ";
        echo "<div class=\"user\">";
        if ($loged) {
            echo (isset($_COOKIE["username"])) ? $_COOKIE["username"] : $_POST["username"];
        }
        else {
            echo "";
        }
        echo "</div></header>";
        
    }

    function printHome($section = "GENERAL"){
        $sectons = array("GENERAL", "Labavo", "Habitacio 1", "Habitacio 2", "Cuina", "Menjador", "Pasadis", "Servidors");
        $sectionsClasses = array();
        $general = FALSE;
        if ($section == "GENERAL") {
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

    function printDatos($LData, $YData, $MData, $DData){
        $section = (isset($_POST["parte"])) ? $_POST["parte"] : "GENERAL" ;
        echo "<div id=\"$section\"><h2>".$section."</h2>";
        printTable($LData, "Últim registre");
        printTable($YData, "Dades de l'any");
        printTable($MData, "Dades del mes");
        printTable($DData, "Dades del dia");
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
            <form method=\"post\" action=\"dadesClimatiques.php\" class=\"login\">
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
            if (checkPasswd($_POST["username"], $_POST["password"])) {
                saveOnSession(array("username" => $_POST["username"],"session_id" => $_POST["password"]));
                setcookie("username", $_POST["username"], time() + (60 * 20));
                setcookie("session_id", session_id(), time() + (60 * 20));
                return (true);
            }
            else {
                return (false);
            }
        }
        elseif (isset($_SESSION) && isset($_COOKIE["session_id"]) && $_COOKIE["session_id"] == session_id()) {
            return (true);
        }
        else {
            return (false);
        }

    }
    
    function checkPasswd($username, $passwd){
        $datos = cargarDatos("../Backend/jsons/usersData.json","json");
        if (isset($datos[$username]) && $passwd == $datos[$username]["passwd"]) {
            return (true);
        }
        else {
            return (false);
        }
    }

    #Procesado de datos
    function procesSetL(array $dataL){
        $LData = array_values($dataL)[0];
        return (array(array("Ubicacio","Data i hora", "T", "H"), array($LData["ubicacio"], $LData["datahora"], $LData["temperatura"], $LData["humitat"])));
    }

    function procesSetD(array $dataD){
        $cleanData = array("header" => array("", "MIN", "MED", "MAX"), "temp" => array("rowTitle" => "T", "min" => 101, "med" => 0, "max" => 0), "hum" => array("rowTitle" => "H", "min" => 101, "med" => 0, "max" => 0));
        $count = 0;
        foreach ($dataD as $id => $reg) {
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