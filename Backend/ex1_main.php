<?php
    include "phpLibs/general_tools.php";
    include "phpLibs/MySQL_PDO.php";

    /**
     * 
     */
    function main(){
        print_r($_POST);
        print_r($_COOKIE);
        scriptAnimBg();
        if (isset($_POST["Surt"])) {
            session_start();
            session_destroy();
            if ($_POST["Surt"] == "Canviar de sessió") {
                unset($_COOKIE);
            }
        }
        $uData = checkLog();
        printNav($uData);
        if (isset($_POST["crea"]) && $uData && $uData["cUsers"]) {
            printRegUserForm();
        }
        elseif ($uData) {
            printModal($uData);
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
        elseif (isset($_SESSION["username"]) || isset($_COOKIE["username"])) {
            printLoginPage((isset($_SESSION["username"])) ? $_SESSION["username"] : $_COOKIE["username"]);
        }
        else {
            printLoginPage();
        }
    }

    # Funciones para generar html
    
    /**
     * Dona la benvinguda als usuaris que no han iniciat sesió o ha caducat hi han de tornar a posar la contrasenya.
     */
    function printNav($loged){
        echo "<header> <a href=\"dadesClimatiques.php\">Sweat Smart Home</a> ";
        if ($loged) {
            echo "<button id=\"userBtn\">".$_SESSION["username"]."</button>";
        }
        echo "</header>";
    }

    function printModal($uData){
        $cUserBtn = "";
        if ($uData["cUsers"]) {
            $cUserBtn = "<input type=\"submit\" value=\"Crear un nou usuari\" name=\"crea\">";
        }
        echo "  <div id=\"myModal\" class=\"modal\">
                    <div class=\"modal-content\">
                        <span class=\"close\">&times;</span>
                        <form action=\"dadesClimatiques.php\" method=\"post\">
                            <legend>Que vols fer?</legend>
                            <input type=\"submit\" value=\"Tancar Sessió\" name=\"Surt\">
                            <input type=\"submit\" value=\"Canviar de sessió\" name=\"Surt\">
                            ".$cUserBtn."
                        </form>
                    </div>
                </div>";
        scriptModal();
        
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

    function printRegUserForm(){
        echo "<p>Siusplau, ingresi el seu nom d'usuari i repeteixi la contrasenya per a registrar aquest copte i logejar amb ell.</p>
            <form method=\"post\" action=\"dadesClimatiques.php\" class=\"login\">
                <label for=\"username\">Nom d'usuari:</label>
                <input type=\"text\" id=\"username\" name=\"username\"><br>
                <label for=\"password\">Contrasenya:</label>
                <input type=\"password\" id=\"password\" name=\"password\"><br><br>
                <input type=\"submit\" value=\"Registrar\" name=\"reg\">
            </form>";
    }

    #Security
    function checkLog(){
        session_start();
        if (isset($_POST["reg"])) {
            $datos = cargarDatos("../Backend/jsons/usersData.json","json");
            $datos[$_POST["username"]] = array("passwd" => $_POST["password"], "cUsers" => false);
            guardarDatos("../Backend/jsons/usersData.json", $datos,"json");
            return($datos[$_POST["username"]]);
        }
        elseif (isset($_POST["username"])) {
            if ($uData = checkPasswd($_POST["username"], $_POST["password"])) {
                saveOnSession(array("username" => $_POST["username"]));
                setcookie("username", $_POST["username"], time() + (60 * 20));
                setcookie("session_id", session_id(), time() + (60 * 20));
                return ($uData);
            }
            else {
                return (false);
            }
        }
        elseif (isset($_SESSION["username"]) && isset($_COOKIE["session_id"]) && $_COOKIE["session_id"] == session_id()) {
            $datos = cargarDatos("../Backend/jsons/usersData.json","json");
            return ($datos[$_SESSION["username"]]);
        }
        else {
            return (false);
        }

    }
    
    function checkPasswd($username, $passwd){
        $datos = cargarDatos("../Backend/jsons/usersData.json","json");
        if (isset($datos[$username]) && $passwd == $datos[$username]["passwd"]) {
            return ($datos[$username]);
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

    #JavaScript

    function scriptModal(){
        echo "<script>
            var modal = document.getElementById(\"myModal\");
            
            var btn = document.getElementById(\"userBtn\");
            
            var span = document.getElementsByClassName(\"close\")[0];

            btn.onclick = function() {
              modal.style.display = \"block\";
            }

            span.onclick = function() {
              modal.style.display = \"none\";
            }
            
            window.onclick = function(event) {
              if (event.target == modal) {
                modal.style.display = \"none\";
              }
            } 
            </script>";
    }

    function scriptAnimBg(){
        $colors = array("Cuina"=>"#8B008B", "Habitacio 1" => "#2E8B57", "Habitacio 2" => "#3EDA0E", "Pasadis" => "#C4C42C", "Servidors" => "#0A3F32", "Menjador" => "#46814B", "Labavo" => "#B49191");
        echo "<div id=\"particles-js\"></div><script src=\"../Backend/js/particles.min.js\"></script>";
        $partConf = cargarDatos("../Backend/jsons/partConfSrc.json","json");
        if (isset($_POST["parte"])) {
            $partConf["particles"]["color"]["value"] = $colors[$_POST["parte"]];
            $partConf["particles"]["line_linked"]["color"] = $colors[$_POST["parte"]];
        }
        echo "<script>particlesJS(".json_encode($partConf).")</script>";
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