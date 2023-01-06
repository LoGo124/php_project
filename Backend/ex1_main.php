<?php
    include "phpLibs/general_tools.php";
    include "phpLibs/MySQL_PDO.php";

    /** 
     * Primer de tot si trobem el POST de tacar la sesió la destruim avans de comprobar si esta logejat, despres comprobem el cas
     * que l'usuari vulguí crear un usuari i si te permisos, si no es així, en cas d'estar logejat printara la pàgina d'inici,
     * la cual depenent de si li pasem una part per la casa o no, mostrara unas dades o altres, finalment si no esta logejat,
     * printara la pàgina de logeig.
     * 
     * @author ilopez
     */
    function main(){
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
                
                $LData = processLast($rawLData);
                $YData = procesSetY($rawYData);
                $MData = procesSet($rawMData);
                $DData = procesSet($rawDData);

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

    #################################################################
    #                       HTML GENERATION                         #
    #################################################################
    
    /** 
     * Genera el header, la barra de navegació, en cas d'estar logejat, tambó posa un botó am el nom de l'usuari i un id
     * 
     * @param * $loged Amb que no sigui equivalent a false ja es suficient per genera el botó. 
     * @author gpal
     */
    function printNav($loged){
        echo "<header> <a href=\"dadesClimatiques.php\">Sweat Smart Home</a> ";
        if ($loged) {
            echo "<button id=\"userBtn\">".$_SESSION["username"]."</button>";
        }
        echo "</header>";
    }

    /** 
     * Printa un modalbox, un div ocult amb un formular, aquest es torna visible cuan s'executa un script.
     * @author ilopez
     */
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

    /** 
     * Genera uns planols d'una casa, amb un formulari que com a "action" s'apunta a si mateix, i un botó per cada part
     * de la casa, i aplica un "class" sobre la secció seleccionada.
     * 
     * @param string $section si la string que es pasa coincideix amb una part de la casa aquesta quedara amb un class per 
     * que s'apliqui un estil diferent, per defecte es "GENERAL", i només en aquest cas, aplica l'class a tots els elements. 
     * @author gpal
     */
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

    /** 
     * Genera un div amb un titol i una clase depenent de la part de la part de la casa pasada pel POST.
     * 
     * @param array $LData array multidimensional amb l'ultim registre i una array dirgida a ser la capçalera de la taula.
     * @param array $YData array multidimensional amb els registres de l'any anterior i una array dirgida a ser la capçalera de la taula.
     * @param array $MData array multidimensional amb els registres d'aquest mes i una array dirgida a ser la capçalera de la taula.
     * @param array $DData array multidimensional amb els registres d'avui i una array dirgida a ser la capçalera de la taula.
     * @author gpal & ilopez4
     */
    function printDatos($LData, $YData, $MData, $DData){
        $section = (isset($_POST["parte"])) ? $_POST["parte"] : "GENERAL" ;
        echo "<div id=\"$section\"><h2>".$section."</h2>";
        printTable($LData, "Últim registre");
        printTable($YData, "Dades de l'any");
        printTable($MData, "Dades del mes");
        printTable($DData, "Dades del dia");
        echo"</div>";
        
    }

    /** 
     * Genera la pàgina de "Login", amb un formulari que com a "action" s'apunta a si mateix, el nom d'usuari 
     * es autocompletable.
     * 
     * @param string $userName Per defecte es una string buida, el que li pasem, sera el valor per defecte del camp "username"
     * @author gpal
     */
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

    /** 
     * Genera la pàgina de "Signup" o registre, amb un formulari que com a "action" s'apunta a si mateix
     * 
     * @author gpal
     */
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

    #################################################################
    #                           SECURITY                            #
    #################################################################

    /** 
     * Inicia la sessió, i comprova els valors de $_POST per decidir si intentar logejar o registrar un nou usuari (sense permisos), 
     * en cas de logeja un nou usuari mantindra la sessió anterior (usuari creador) oberta, en cas de ser un intent de "login"
     * i de ser correcta la contrasenya, guarda el "username" a la sessió i a la cookie, pero també és molt important que a mes,
     * a la cookie guarda l'identificador de la sessió (l'ús s'explica en el main) i finalment, en cas d trobar les dades ja emmagatzemades,
     * retorna true
     * 
     * @return array|bool en cas de logejar correctament, retorna les dades de l'usuari, sino, retorna false.
     * @author gpal
     */
    function checkLog(){
        session_start();
        if (isset($_POST["reg"])) {
            $datos = cargarDatos("../Backend/jsons/usersData.json","json");
            $datos[$_POST["username"]] = array("passwd" => $_POST["password"], "cUsers" => false);
            guardarDatos("../Backend/jsons/usersData.json", $datos,"json");
            return($datos[$_SESSION["username"]]);
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
    
    /** 
     * En cas de que el nom d'usuari i contrasenya siguin correctes, retorna les dades de l'usuari trobades al mateix 
     * fitxer que hem utilitzat per comprobar la contrasenya, en cas contrari retorna false.
     * 
     * @param string $username El nom d'usuari del que volem comprobar la contrasenya i/o rebre les dades.
     * @param string $passwd La contrasenya que correspon a l'usuari, es necesaria per retornar les dades.
     * @return array|bool Retorna false en cas de no coïncidir la contrasenya i en cas que sí, retorna les dades de l'usuari,
     * trobades a l'arxiu, usersData.json com ara el permis per crear usuaris.
     * @author gpal
     */
    function checkPasswd($username, $passwd){
        $datos = cargarDatos("../Backend/jsons/usersData.json","json");
        if (isset($datos[$username]) && $passwd == $datos[$username]["passwd"]) {
            return ($datos[$username]);
        }
        else {
            return (false);
        }
    }

    #################################################################
    #                     PROCESADO DE DATOS                        #
    #################################################################

    /** 
     * Prepara les dades de l'ultim registre rebut de la base de dades en un format de array multidimensional, afegint-li les capçeleres.
     * 
     * @param array $dataL Rep les dades crude de la resposta de la base de dades.
     * @return array l'array multidimensional preparada per ser mostrada en forma de taula.
     * @author gpal
     */
    function processLast(array $dataL){
        $LData = array_values($dataL)[0];
        return (array(array("Ubicacio","Data i hora", "T", "H"), array($LData["ubicacio"], $LData["datahora"], $LData["temperatura"], $LData["humitat"])));
    }

    /**
     * Prepara les dades mensuals o diaries rebudes de la base de dades en un format de array multidimensional, afegint-li les capçeleres.
     * 
     * @param array $data Rep les dades crude de la resposta de la base de dades.
     * @return array l'array multidimensional preparada per ser mostrada en forma de taula.
     * @author ilopez
     */
    function procesSet(array $data){
        $cleanData = array("header" => array("", "MIN", "MED", "MAX"), "temp" => array("rowTitle" => "T", "min" => 101, "med" => 0, "max" => 0), "hum" => array("rowTitle" => "H", "min" => 101, "med" => 0, "max" => 0));
        $count = 0;
        foreach ($data as $id => $reg) {
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

    /**
     * Prepara les dades anuals rebudes de la base de dades en un format de array multidimensional, afegint-li les capçeleres.
     * 
     * @param array $dataY Rep les dades crude de la resposta de la base de dades.
     * @return array l'array multidimensional preparada per ser mostrada en forma de taula.
     * @author ilopez
     */
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

    #################################################################
    #                          JAVASCRIPT                           #
    #################################################################

    /** 
     * Insereix un script que permet que amb el botó amb el nom d'usuari, es fagi visible un formulari ocult.
     * 
     * @author ilopez
     */
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

    /** 
     * Posa un div que fara de fons animat, carrega la configuracio per defecte d'un arxiu json i l'edita canviant el color
     * de les particules en cas d'haber selecciont una part de la casa en espacific. 
     * 
     * @author ilopez
     */
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

    #################################################################
    #                           DEV TOOLS                           #
    #################################################################

    /** 
     * Fa ús de la llibreria MySQL_PDO per connectar a la base de dades local, crear una base de dades, 2 taules dades_mes
     * i dades_any i a continuació en cas de estar descomentat, crida a la funció genRegs().
     * 
     * @author ilopez
     */
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

    /** 
     * Fa ús de la mateixa llibreria per inserir a les dues taules un molt complert conjunt de registres
     * dades_mes: Genera 3 registres diaris amb valors aleatoris per cada habitació en 3 momens diferents del día.
     * dades_any: Genera 1 registre diari amb valors aleatoris per cada habitació 
     * 
     * @author gpal
     */
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