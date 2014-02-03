<?PHP
// Evito CACHE
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Inicio Session
session_start();
//ini_set('display_errors', 1);
include_once('app.config.php');
include_once('sitio.config.php');
include_once(DIR_BASE.'funciones-auxiliares.php');
include(DIR_LIB.'nyiLIB.php');
include(DIR_LIB.'nyiHTML.php');
include(DIR_LIB.'nyiDATA.php');
include_once(DIR_BASE.'seguridad/usuario.class.php');
include_once(DIR_BASE.'class/interfaz.class.php'); 
include_once('funciones-sitio.php');
require_once(DIR_XAJAX.'xajax_core/xajax.inc.php');

$interfaz = new Interfaz();
$seccion = new nyiHTML('cancelar-compra.htm');

// Extraigo parametros
$idVenta = $_GET["id"];
$token = $_GET["token"];

// Valido id numerico y mayor que 0, y que el token sea de 10 caracteres
if(intval($idVenta) > 0 && strlen($token) == 10){
	// Ahora hay que ver si el id pertenece a una venta registrada con este token
	$datosVenta = $interfaz->obtenerDatosVenta($idVenta);
	if(is_array($datosVenta) && $token == $datosVenta["cabezal"]["codigo_cancelacion"]){
		if($_SERVER["REQUEST_METHOD"] == "POST"){
			$res = $interfaz->cancelarVenta($idVenta, $token);
			if($res == ""){
				$seccion->assign("cancelada", _SI);	
			}
			else {
				$seccion->assign("error", "Ocurrió un error y no se pudo cancelar la orden de compra.");
				LogArchivo("Error al cancelar la venta $idVenta con token $token\n" . $res);
			}
		}
		else{
			$subtotalDsc = number_format($datosVenta["cabezal"]["total"]-$datosVenta["cabezal"]["costo_envio"], 2);
			$costoDsc = number_format($datosVenta["cabezal"]["costo_envio"], 2);
			$totalDsc = number_format($datosVenta["cabezal"]["total"], 2);
			$tablaDetalles = new nyiHTML("tabla-detalles-checkout.htm");
			$tablaDetalles->assign("subtotal", $subtotalDsc);
			$tablaDetalles->assign("costo_envio", $costoDsc);
			$tablaDetalles->assign("total",  $totalDsc);
			$detalles = array();
			foreach($datosVenta["items"] as $item){
				array_push($detalles, array(
					'nombre'=>$item['nombre_prenda'],
					'talle'=>$item['codigo'],
					'color'=>$item['nombre_color'],
					'cantidad'=>$item['cantidad'],
					'unitario'=>$item['precio'],
					'total'=>$item['subtotal']
				));
			}
			
			// Datos de la compra
			$seccion->assign("datosPago",  $datosVenta["cabezal"]["medio_pago"] ? "PAGO ONLINE OCA CARD" : "ABITAB");
			$seccion->assign("estadoPago",  strtoupper($datosVenta["cabezal"]["estado_pago"]));
			$seccion->assign("estadoVenta",  strtoupper($datosVenta["cabezal"]["estado_venta"]));
			$tablaDetalles->assign("items", $detalles);
			$seccion->assign('detallesVenta', $tablaDetalles->fetchHTML());
			$seccion->assign('idVenta', $idVenta);
			$seccion->assign("subtotal", $subtotalDsc);
			$seccion->assign("costo_envio", $costoDsc);
			$seccion->assign("total",  $totalDsc);
			$seccion->assign("fecha",  FormatDateLong($datosVenta["cabezal"]["fecha"]));
			
			// Datos del comprador
			$datosComprador = $datosVenta["cabezal"]["invitado"] == 1 ? $datosVenta["invitado"] : $datosVenta["comprador"];
			$seccion->assign("nombre", $datosComprador['nombre']);
			$seccion->assign("apellido", $datosComprador['apellido']);
			$seccion->assign("email", $datosComprador['email']);
			$seccion->assign("direccion", $datosComprador['direccion']);
			$seccion->assign("departamento", $datosComprador['departamento']);
			$seccion->assign("ciudad", $datosComprador['ciudad']);
			$seccion->assign("codigo_postal", $datosComprador['codigo_postal']);
			$seccion->assign("telefono", $datosComprador['telefono']);
		}
	}
	else {
		header("Location: index.php");
	}
}
else {
	header("Location: index.php");
}

$xajax = new xajax();
$xajax->setRequestURI('ventas-ajaxhelper.php');
$xajax->registerFunction('llenarCarritoLocal');
$xajax->registerFunction("calcularEnvio");
$xajax->processRequest();
$seccion->assign('AJAX_JAVASCRIPT', $xajax->getJavascript(DIR_XAJAX));

$seccion->printHTML();

?>