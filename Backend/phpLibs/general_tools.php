<?php

/**
 * Busca un valor en una array de manera recursiva, es pot demanar que retorni la key vinculada al valor.
 * 
 * Exemple: 
 *  array = array("username"=>"ipuli","password"=>"qwerty")
 *  checkValue("qwerty") retorna "password"
 */
function checkValue($findValue, $array, $returnKey = FALSE){
    foreach ($array as $key => $value) {
        if ($value == $findValue && !($returnKey)) {
            return TRUE;
        }
        elseif ($value == $findValue && $returnKey){
            return $key;
        }
        elseif (gettype($value) == "array") {
            if (checkValue($findValue, $value) && !($returnKey)){
                return TRUE;
            }
            elseif (checkValue($findValue, $value) && $returnKey) {
                return array($key,checkValue($findValue, $value, $returnKey=TRUE));
            }
        }
    }
    if ($returnKey){
        return array(FALSE,array(FALSE));
    }
    else {
        return FALSE;
    }
}

/**
 * Cerca el valor entrat com a key de la string i retorna TRUE o FALSE si no hi es present, imita el comportament de la funció array_key_exists
 * 
 * @param string $findKey Valor que buscara
 * @param array $array Array a la que busca la key
 */
function checKey($findKey, $array, $returnValue = FALSE){
    foreach ($array as $key => $value) {
        if ($key == $findKey && !($returnValue)){
            return TRUE;
        }
        elseif ($key == $findKey && $returnValue) {
            return $value;
        }
    }
    return FALSE;
}

/**
     * Guarda les dades de la llista entrada en la ruta entrada amb el format escollit, permet serialitzat i json.
     * 
     * @param string $path ruta de l'arxiu
     * @param array $lista dades per guardar
     * @param string $format 
     */
function guardarDatos($path, array $lista, $format = "S"){
    $oFile = fopen($path,"w");
    switch ($format) {
        case "S":
            fwrite($oFile,serialize($lista));
            break;
        case "json":
            fwrite($oFile,json_encode($lista));
            break;
        }
    fclose($oFile);
}

/**
 * Carrega les dades de la ruta entrada amb el format escollit, permet serialitzat i json, i els retorna.
 * 
 *  @param string $path ruta de l'arxiu
 *  @param string $format
 *  @return array $lista llista d'objectes
 */
function cargarDatos($path, $format = "S"){
    $oFile = fopen($path,"r");
    switch ($format) {
        case 'S':
            $lista = unserialize(fread($oFile,filesize($path)));
            break;
        case 'json':
            $lista = json_decode(fread($oFile,filesize($path)),TRUE);
            break;
    }
    fclose($oFile);
    return $lista;
}

# Reseteja el contingut i l'id de una sessio.
function resetSessio(){
    session_destroy();
    session_start();
    session_regenerate_id(true);
}

# Guarda la llista entrada, una funció una mica inutil
function saveOnSession(array $contingut){
    foreach ($contingut as $key => $value) {
        $_SESSION[$key] = $value;
    }
}

# Esborrar les dades amb keys a la llista entrada
function delOnSession($keys){
    foreach ($keys as $realKey => $key) {
        if (checKey($key, $_SESSION)){
            unset($_SESSION[$key]);
        }
        else {
            echo "Error not found key on session.";
        }
    }
}

function implodeKeyVal(array $arrayAsoc = null, string $sep = "", string $sepKeyVal = "", string $header = "", string $footer = ""){
    $str = $header;
    $unformated = array();
    foreach ($arrayAsoc as $key => $value) {
        array_push($unformated ,$key.$sepKeyVal.$value);
    }
    $str .= implode($sep,$unformated).$footer;
    return $str;
}

function printError(string $var = "Unknown"){
    if ($var == "Unknown") {
        echo "<h1> ERROR DESCONEGUT </h1>";
    }
    else {
        echo "<h1> ERROR $var </h1>";
    }
}

function printTable($data, $titol = ""){
    if ($titol) {
        echo "<div id=\"$titol\"><h3>$titol</h3>";
    }
    echo "<table>";
    // recorre el array de datos fila a fila
    foreach ($data as $row) {
      echo "<tr>";
      // recorre cada celda de la fila
      foreach ($row as $cell) {
        echo "<td>$cell</td>";
      }
      echo "</tr>";
    }
    echo "</table>";
    if ($titol) {
        echo "</div>";
    }
}
?>