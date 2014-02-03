<?PHP
// Evito CACHE
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Inicio Session
session_start();
ini_set('display_errors', 1);
include_once('app.config.php');
include_once('sitio.config.php');
include_once(DIR_BASE.'funciones-auxiliares.php');
include(DIR_LIB.'nyiLIB.php');
include(DIR_LIB.'nyiHTML.php');
include(DIR_LIB.'nyiDATA.php');
include_once(DIR_BASE.'seguridad/usuario.class.php');
include_once(DIR_BASE.'class/interfaz.class.php'); 
include_once(DIR_BASE.'class/barcode39.class.php'); 
include_once('funciones-sitio.php');
include(DIR_LIB .'nusoap/nusoap.php');

$client = new soapclient(URL_PROCESADOR_ABITAB . '?wsdl');
// Check for an error
$err = $client->getError();
if ($err) {
    // Display the error
    echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
    // At this point, you know the call that follows will fail
}
/*

$interfaz = new Interfaz();
$cod25 = $interfaz->obtenerString25(40);
$cod24 = $interfaz->obtenerString24(40, $cod25);
echo $cod25 . $cod24;

$reg1 = $cod25.$cod24."000000817501003819092012";
$cod25 = $interfaz->obtenerString25(27);
$cod24 = $interfaz->obtenerString24(27, $cod25);
$reg2 = $cod25.$cod24."000000817501003819092012";
$cod25 = $interfaz->obtenerString25(34);
$cod24 = $interfaz->obtenerString24(34, $cod25);
$reg3 = $cod25.$cod24."000000113501003820092012";
$resultLogin = $client->call('login', array('user' => 'admprili', 'pass' => 'admin'));
if($resultLogin > 0){
	$result = $client->call('procesarArchivo', array('contenidoArchivo' => $reg1.$reg2.$reg3));
}
// Check for a fault
if ($client->fault) {
    echo '<h2>Fault</h2><pre>';
    print_r($result);
    echo '</pre>';
} else {
    // Check for errors
    $err = $client->getError();
    if ($err) {
        // Display the error
        echo '<h2>Error</h2><pre>' . $err . '</pre>';
    } else {
        // Display the result
        echo '<h2>Result</h2><pre>';
        print_r($result);
		echo '</pre>';
    }
}
*/
//echo sys_get_temp_dir();
//$interfaz = new Interfaz();
//$cod25 = $interfaz->obtenerString25(27);
//echo $cod25;
//$cod24 = $interfaz->obtenerString24(27, $cod25);
//$bc = new Barcode39($cod24);
// set text size
//$bc->barcode_text_size = 5;
// set barcode bar thickness (thick bars)
//$bc->barcode_bar_thick = 4;
// set barcode bar thickness (thin bars)
//$bc->barcode_bar_thin = 2; 
//$bc->draw();
//echo $interfaz->generarDigitoControl("XXX010149600003681009200100000012300102010000012");
//*PRI0000012000002700000000*
//echo $interfaz->generarDigitoControl($interfaz->obtenerString23(27), $interfaz->obtenerString25(27));

?>