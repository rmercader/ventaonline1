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

function validarItemCompra($idPrenda, $idColor, $codTalle, $cantidad)
{
	$objResponse = new xajaxResponse();
	$interface = new Interfaz();
	$idTalle = $interface->obtenerIdTallePorCodigo($codTalle);
	$cantStock = $interface->obtenerCantidadStock($idPrenda, $idColor, $idTalle);
	
	if($cantStock >= $cantidad){
		$objResponse->assign("infostock", "innerHTML", "En stock");
		$objResponse->assign("infostock", "style.color", "black");
		$objResponse->assign("haystock", "value", 1);
		
		$id = "$idPrenda-$idColor-$idTalle";
		// Seteamos el valor de cantidad maxima
		$quantityMaxName = FuncionesSitio::obtenerVerificacionHMAC('quantity_max', $cantStock, $idPrenda);
		$objResponse->assign("quantity_max", "value", $cantStock);
		$objResponse->assign("quantity_max", "name", "quantity_max{$quantityMaxName}");

		// Seteamos el id
		$idName = FuncionesSitio::obtenerVerificacionHMAC('id', $id, $idPrenda);
		$objResponse->assign("id", "value", $id);
		$objResponse->assign("id", "name", "id{$idName}");
	}
	else{
		$objResponse->assign("infostock", "innerHTML", "No hay stock");
		$objResponse->assign("infostock", "style.color", "red");
		$objResponse->assign("haystock", "value", 0);
	}
	return $objResponse;
}

function listarPrendasCategoria($idCategoria, $pagina){
	$interface = new Interfaz();
	$objResponse = new xajaxResponse();
	$prendas = $interface->obtenerPrendasPorCategoria($idCategoria, $pagina);
	$cantResultados = $prendas["cantidad"];
	$totalPaginas = ceil($cantResultados/PRENDAS_POR_PAGINA);
	$html = FuncionesSitio::generarHtmlPaginaPrendas($prendas["datos"]);
	$objResponse->assign("grilla-prendas", "innerHTML", $html);
	$objResponse->assign("pagina", "value", $pagina);
	if($pagina > 0){
		$objResponse->assign('ant', "style.display", 'block');
	}
	else{
		$objResponse->assign('ant', "style.display", 'none');
	}
	if($pagina < ($totalPaginas-1)){
		$objResponse->assign('sig', "style.display", 'block');
	}	
	else{
		$objResponse->assign('sig', "style.display", 'none');
	}
	return $objResponse;
}

function listarPrendasColeccion($idColeccion, $pagina){
	$interface = new Interfaz();
	$objResponse = new xajaxResponse();
	$prendas = $interface->obtenerPrendasPorColeccion($idColeccion, $pagina);
	$cantResultados = $prendas["cantidad"];
	$totalPaginas = ceil($cantResultados/PRENDAS_POR_PAGINA);
	$html = FuncionesSitio::generarHtmlPaginaPrendas($prendas["datos"]);
	$objResponse->assign("grilla-prendas", "innerHTML", $html);
	$objResponse->assign("pagina", "value", $pagina);
	if($pagina > 0){
		$objResponse->assign('ant', "style.display", 'block');
	}
	else{
		$objResponse->assign('ant', "style.display", 'none');
	}
	if($pagina < ($totalPaginas-1)){
		$objResponse->assign('sig', "style.display", 'block');
	}	
	else{
		$objResponse->assign('sig', "style.display", 'none');
	}
	return $objResponse;
}

// @param $items es el array JSONP que te manda el foxycart
function llenarCarritoLocal($items){
	$objResponse = new xajaxResponse();
	$interface = new Interfaz();
	// Asi se obtienen los items
	foreach($items as $prenda){
		$idPrenda = $prenda["code"];
		$nombre = $prenda["name"];
		$color = $prenda["options"]["color"]; // $idColor - $nombreColor
		$talle = $prenda["options"]["talle"]; // Codigo talle
		$precio = $prenda["price"];
		$cantidad = $prenda["quantity"];
	}
	return $objResponse;
}

function obtenerMinimoParaCompra(){
	$objResponse = new xajaxResponse();
	$interface = new Interfaz();
	$cotiza = $interface->obtenerConversionMoneda();
	$minimoPesos = $interface->obtenerMontoMinimoVenta();
	$minimo = number_format(round($minimoPesos * $cotiza, 2), 2);
	$objResponse->call('setearMontoMinimoDeCompraEnCarrito', $minimoPesos, $minimo);
	return $objResponse;
}

// Ajax
$xajax = new xajax();
$xajax->registerFunction("validarItemCompra");
$xajax->registerFunction("listarPrendasCategoria");
$xajax->registerFunction("listarPrendasColeccion");
$xajax->registerFunction("llenarCarritoLocal");
$xajax->registerFunction("obtenerMinimoParaCompra");
$xajax->processRequest();
$xajax->printJavascript(DIR_XAJAX);

?>