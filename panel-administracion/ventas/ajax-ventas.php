<?PHP

session_start();

// Includes
include('../../app.config.php');
include('../admin.config.php');

// Incluyo funcionalidades comunes
require_once("../xajax/xajax_core/xajax.inc.php");
include(DIR_LIB.'nyiLIB.php');
include(DIR_LIB.'nyiHTML.php');
include(DIR_LIB.'nyiDATA.php');
include(DIR_LIB.'nyiPDF.php');
include(DIR_BASE.'funciones-auxiliares.php');

include_once(DIR_BASE.'seguridad/seguridad.class.php');
include_once(DIR_BASE.'ventas/venta.class.php');

$xajax = new xajax();

function modificarEstado($id, $estadoVal){
	$objResponse = new xajaxResponse(); // Creo objeto Response
	// Validaciones
	$idVenta = (int)$id;
	if(in_array($estadoVal, array('iniciada', 'enviada', 'completa'))){
		$estado = $estadoVal;
		$Cnx = nyiCNX(); // Creo la conexion
		$vta = new Venta($Cnx, $xajax);
		$res = $vta->modificarEstado($idVenta, $estado);
		if($res == ""){ 
			$objResponse->alert("El estado se ha modificado correctamente");
		}
		else {
			$objResponse->alert("El estado no se ha podido modificar debido a un error interno.");
			LogArchivo($res);
		}
	}
	else {
		$objResponse->alert("El estado es incorrecto.");
	}
	
	return $objResponse;
}

// Ajax
$xajax->registerFunction("modificarEstado");
$xajax->processRequest();
$xajax->printJavascript(DIR_XAJAX_PARA_ADMIN);

?>