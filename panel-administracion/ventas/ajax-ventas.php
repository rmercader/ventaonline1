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
			$objResponse->alert("El estado de la venta se ha modificado correctamente");
		}
		else {
			$objResponse->alert("El estado de la venta no se ha podido modificar debido a un error interno.");
			LogArchivo($res);
		}
	}
	else {
		$objResponse->alert("El estado de la venta es incorrecto.");
	}
	
	return $objResponse;
}

function modificarEstadoPago($id, $estadoVal){
	$objResponse = new xajaxResponse(); // Creo objeto Response
	// Validaciones
	$idVenta = (int)$id;
	if(in_array($estadoVal, array('pendiente', 'confirmado'))){
		$estado = $estadoVal;
		$Cnx = nyiCNX(); // Creo la conexion
		$vta = new Venta($Cnx, $xajax);
		$res = $vta->modificarEstadoPago($idVenta, $estado);
		if($res == ""){ 
			$objResponse->alert("El estado del pago se ha modificado correctamente");
		}
		else {
			$objResponse->alert("El estado del pago no se ha podido modificar debido a un error interno.");
			LogArchivo($res);
		}
	}
	else {
		$objResponse->alert("El estado del pago es incorrecto.");
	}
	
	return $objResponse;
}

// Ajax
$xajax->registerFunction("modificarEstado");
$xajax->registerFunction("modificarEstadoPago");
$xajax->processRequest();
$xajax->printJavascript(DIR_XAJAX_PARA_ADMIN);

?>