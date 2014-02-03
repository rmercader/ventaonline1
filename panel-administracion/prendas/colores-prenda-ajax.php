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

// Conexion con la base de datos
$Cnx = nyiCNX();
$Cnx->debug = false;
$xajax = new xajax();

include_once(DIR_BASE.'seguridad/seguridad.class.php');
include(DIR_BASE.'prendas/prenda.class.php');

function eliminarAsociacionColor($idPrenda, $idColor){
	$objResponse = new xajaxResponse(); // Creo objeto Response
	$Cnx = nyiCNX(); // Creo la conexion
	$prenda = new Prenda($Cnx);
	$prenda->eliminarAsociacionColor($idPrenda, $idColor);
	$objResponse->assign("container", "innerHTML", $prenda->obtenerHtmlColoresAsociados($idPrenda));
	$objResponse->call("prepararBorrados");
	return $objResponse;
}

// Busca en los colores que no esten asociados a la prenda
function buscarColoresPorNombreParaAgregar($nomBuscarColor, $idPrenda){
	$objResponse = new xajaxResponse(); // Creo objeto Response
	$Cnx = nyiCNX(); // Creo la conexion
	$prenda = new Prenda($Cnx);
	$resColores = $prenda->obtenerColoresPorNombreParaAgregar($nomBuscarColor, $idPrenda);
	$html = new nyiHTML('prendas/lista-colores-resultado.htm');
	$html->assign('LARGO_THU_COLOR', LARGO_THU_COLOR);
	$html->assign('ANCHO_THU_COLOR', ANCHO_THU_COLOR);
	while(!$resColores->EOF){
		$idColor = $resColores->fields['id_color'];
		$nombre = $resColores->fields['nombre_color'];
		$html->append('REG', array('id_color'=>$idColor, 'nombre_color'=>$nombre, 'url_thumb_color'=>DIR_HTTP . "prendas/foto-color.php?thumb=1&id=$idColor"));
		$resColores->MoveNext();
	}
	$objResponse->assign("resultados", "innerHTML", $html->fetchHTML());
	return $objResponse;
}

if(isset($_GET['term'])){
	$nomBuscarColor = $_GET['term'];
	$idPrenda = intval($_SESSION["id_prenda"]);
	$prenda = new Prenda($Cnx);
	$resColores = $prenda->obtenerColoresPorNombreParaAgregar($nomBuscarColor, $idPrenda);
	$html = new nyiHTML('prendas/lista-colores-resultado.htm');
	$html->assign('LARGO_THU_COLOR', LARGO_THU_COLOR);
	$html->assign('ANCHO_THU_COLOR', ANCHO_THU_COLOR);
	$nombres = array();
	while(!$resColores->EOF){
		$idColor = $resColores->fields['id_color'];
		$nombre = $resColores->fields['nombre_color'];
		$html->append('REG', array('id_color'=>$idColor, 'nombre_color'=>$nombre, 'url_thumb_color'=>DIR_HTTP . "prendas/foto-color.php?thumb=1&id=$idColor"));
		array_push($nombres, array("label"=>$nombre, "value"=>"$idColor - $nombre", "image"=>DIR_HTTP . "prendas/foto-color.php?thumb=1&id=$idColor"));
		//array_push($nombres, $nombre);
		$resColores->MoveNext();
	}
	echo json_encode($nombres);
	//echo "[" . implode(",", $nombres) . "]";
	//$html->printHTML();
}
else{
	// Ajax
	$xajax->registerFunction("eliminarAsociacionColor");
	$xajax->registerFunction("buscarColoresPorNombreParaAgregar");
	$xajax->processRequest();
	$xajax->printJavascript(DIR_XAJAX_PARA_ADMIN);
}

?>