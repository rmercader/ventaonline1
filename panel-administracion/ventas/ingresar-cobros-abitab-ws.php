<?PHP

include_once('../app.config.php');
include_once(DIR_BASE.'configuracion-inicial.php');
include_once(DIR_LIB . 'nyiDATA.php');
include_once(DIR_LIB . 'nusoap/nusoap.php');
include_once(DIR_BASE . 'funciones-auxiliares.php');
include_once(DIR_BASE . 'seguridad/usuario.class.php');
include_once(DIR_BASE . 'ventas/venta.class.php');
ini_set('display_errors', 1);
// Inicio Session
session_start();
$error = "";
$html = new nyiHTML('ventas/ingresar-cobros-abitab.htm');

// Si viene con POST, submit del archivo
if($_SERVER['REQUEST_METHOD'] == 'POST'){
	$client = new soapclient(URL_PROCESADOR_ABITAB . '?wsdl');
	
	$client->call('login', array('user'=>USUARIO_WS, 'pass'=>CLAVE_USUARIO_WS));
	$result = $client->call('procesarArchivo', array('contenidoArchivo' => file_get_contents($_FILES['archivo']['tmp_name'])));
	if ($client->fault) {
		LogArchivo("Fault al ejecutar WS procesarArchivo:\n" . print_r($result, true));
		$error .= "Ocurrio un error al procesar el archivo: " . $result['faultstring'];
	} 
	else {
		$err = $client->getError();
		if ($err) {
			LogArchivo("Error al ejecutar WS procesarArchivo:\n$err");
			$error .= "Ocurrio un error al procesar el archivo.";
		} 
		else {
			$error = "El archivo fue procesado correctamente";
		}
	}
}

$html->assign('error', $error);
// Cabezal
$Cab = new nyiHTML('base_cabezal_abm.htm');
$Cab->assign('NOMFORM', 'INGRESO DE ARCHIVO DE PAGOS ENVIADO POR ABITAB');
$Cab->assign('NOMACCION', getNomAccion(ACC_POST));
$Cab->assign('ACC', ACC_POST);
$Cab->assign('SCRIPT_SALIR', basename($_SERVER['SCRIPT_NAME']));
$html->assign('NAVEGADOR', $Cab->fetchHTML());
$mod_Contenido = $html->fetchHTML();

?>