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
$marco = new nyiHTML('masterpage.htm');
$seccion = new nyiHTML('prenda-detalle.htm');
$idCategoria = 0;
$idLinea = 0;
$xajax = new xajax();
$xajax->registerFunction("validarItemCompra");
$xajax->registerFunction("obtenerMinimoParaCompra");
$xajax->setRequestURI('prendas-ajaxhelper.php');
$xajax->processRequest();

if(isset($_GET['prenda'])){
	$idPrenda = intval($_GET['prenda']);
	$prenda = $interfaz->obtenerDatosPrenda($idPrenda);
	if(count($prenda) == 0){
		header("Location: index.php");
		exit();
	}
	$idCategoria = intval($prenda[0]['id_categoria_prenda']);
	$categoria = $interfaz->obtenerDatosCategoria($idCategoria, array('id_linea'));
	$idLinea = intval($categoria[0]['id_linea']);
	$arrCategorias = $interfaz->obtenerCategoriasConPrendasPorLinea($idLinea, array('id_categoria_prenda', 'nombre_categoria_prenda'));
	$arrColecciones = $interfaz->obtenerColeccionesConPrendasPorLinea($idLinea);
	$menu = FuncionesSitio::generarMenuParaPrendas($arrCategorias, $arrColecciones);
	$seccion->assign('submenu', $menu);
	
	// Ahora si, los datos de la prenda
	$seccion->assign('id_prenda', $idPrenda);
	$seccion->assign('nombre_prenda', $prenda[0]['nombre_prenda']);
	
	// Se obtiene la cotizacion de la moneda de la transaccion
	$cotiza = $interfaz->obtenerConversionMoneda();
	if(is_numeric($cotiza)){
		$precio = $prenda[0]['precio'] * $cotiza;
	}
	else{
		$precio = $prenda[0]['precio'];
	}
	
	$seccion->assign('precioLocal', $prenda[0]['precio']);
	$seccion->assign('precio', $precio);
	$seccion->assign('descripcion', $prenda[0]['descripcion']);
	
	// Para la validacion HMAC 
	$seccion->assign('idPrendaName', FuncionesSitio::obtenerVerificacionHMAC('code', $idPrenda, $idPrenda));
	$seccion->assign('nombrePrendaName', FuncionesSitio::obtenerVerificacionHMAC('name', $prenda[0]['nombre_prenda'], $idPrenda));
	$seccion->assign('precioName', FuncionesSitio::obtenerVerificacionHMAC('price', $precio, $idPrenda));
	$seccion->assign('descripcionName', FuncionesSitio::obtenerVerificacionHMAC('description', $prenda[0]['descripcion'], $idPrenda));
	$seccion->assign('quantityName', FuncionesSitio::obtenerVerificacionHMAC('quantity', "--OPEN--", $idPrenda));
	
	$fotos = $interfaz->obtenerFotosPrenda($idPrenda);
	$seccion->assign('lstFotos', $fotos);
	$urlPrimera = $fotos[0];
	$extension = strrchr($urlPrimera, '.');
	$pos = strrpos ($urlPrimera, $extension);
	$urlThumbnail = substr_replace ($urlPrimera, "", $pos);
	$urlThumbnail .= "-thu" . $extension;
	
	$datosColores = $interfaz->obtenerColoresPrenda($idPrenda);
	foreach($datosColores as $color){
		$seccion->append('lstThuColores', $color);
		$miIdColor = $color['id_color'] . ' - ' .$color['nombre_color'];
		$seccion->append('ids_colores', $miIdColor . "||" . hash_hmac('sha256', $idPrenda . 'color' . $miIdColor, API_KEY));
		$seccion->append('dsc_colores', $color['nombre_color']);
		//$seccion->append('ids_colores', $miIdColor);
	}
	
	$datosTalles = $interfaz->obtenerTallesPrenda($idPrenda);
	foreach($datosTalles as $talle){
		$seccion->append('ids_talles', $talle['codigo'] . "||" . hash_hmac('sha256', $idPrenda . 'talle' . $talle['codigo'], API_KEY));
		$seccion->append('dsc_talles', $talle['codigo']);
		//$seccion->append('ids_talles', $talle['codigo']);
	}
}
else{
	header("Location: index.php");
	exit();
}

$seccion->assign('LARGO_FOTO_PRENDA', LARGO_FOTO_PRENDA);
$seccion->assign('ANCHO_FOTO_PRENDA', ANCHO_FOTO_PRENDA);
$seccion->assign('LARGO_IMG_COLOR', LARGO_IMG_COLOR);
$seccion->assign('ANCHO_IMG_COLOR', ANCHO_IMG_COLOR);
$seccion->assign('LARGO_THU_COLOR', LARGO_THU_COLOR);
$seccion->assign('ANCHO_THU_COLOR', ANCHO_THU_COLOR);
//$seccion->assign('url_thu_prenda', $urlThumbnail);
$seccion->assign('AJAX_JAVASCRIPT', $xajax->getJavascript(DIR_XAJAX));
$seccion->assign('imgFondo', FuncionesSitio::imagenFondoLinea($idLinea));

$marco->assign('contenido_seccion', $seccion->fetchHTML());
$marco->printHTML();

?>