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
$seccion = new nyiHTML('prendas.htm');

$xajax = new xajax();
$xajax->registerFunction("listarPrendasCategoria");
$xajax->registerFunction("listarPrendasColeccion");
$xajax->setRequestURI('prendas-ajaxhelper.php');
$xajax->processRequest();
$marco->assign('AJAX_JAVASCRIPT', $xajax->getJavascript(DIR_XAJAX));

$idCategoria = 0;
$idColeccion = 0;
$idLinea = 0;
$nombreCategoria = "";
$nombreColeccion = "";

if(isset($_GET['linea'])){
	$idLinea = intval($_GET['linea']);
	$arrCategorias = $interfaz->obtenerCategoriasConPrendasPorLinea($idLinea, array('id_categoria_prenda', 'nombre_categoria_prenda'));
	$idCategoria = (is_array($arrCategorias) && count($arrCategorias) > 0) ? $arrCategorias[0]['id_categoria_prenda'] : 0;
	$nombreCategoria = $arrCategorias[0]['nombre_categoria_prenda'];
}
elseif(isset($_GET['categoria'])){
	$idCategoria = intval($_GET['categoria']);
	$categoria = $interfaz->obtenerDatosCategoria($idCategoria, array('nombre_categoria_prenda', 'id_linea'));
	$idLinea = intval($categoria[0]['id_linea']);
	$nombreCategoria = $categoria[0]['nombre_categoria_prenda'];
	$arrCategorias = $interfaz->obtenerCategoriasConPrendasPorLinea($idLinea, array('id_categoria_prenda', 'nombre_categoria_prenda'));
}
elseif(isset($_GET['coleccion'])){
	$idColeccion = intval($_GET['coleccion']);
	$coleccion = $interfaz->obtenerDatosColeccion($idColeccion, array('nombre_coleccion', 'id_linea'));
	$idLinea = intval($coleccion[0]['id_linea']);
	$nombreColeccion = $coleccion[0]['nombre_coleccion'];
	$arrCategorias = $interfaz->obtenerCategoriasConPrendasPorLinea($idLinea, array('id_categoria_prenda', 'nombre_categoria_prenda'));
}
// Datos descriptivos de la linea
$nomLinea = $interfaz->obtenerNombreLinea($idLinea);
$seccion->assign('nombreLinea', $nomLinea);

// Categorias y Colecciones con prendas de la linea
$arrColecciones = $interfaz->obtenerColeccionesConPrendasPorLinea($idLinea);
$menu = FuncionesSitio::generarMenuParaPrendas($arrCategorias, $arrColecciones);
$seccion->assign('submenu', $menu);

// Paginacion
$pagina = 0;
if(isset($_GET['pagina'])){
	$pagina = intval($_GET['pagina']);
}

if($idCategoria > 0){
	$seccion->assign('nombreListado', "Prendas de la categoría $nombreCategoria");
	$urlFoto = $interfaz->obtenerUrlFotoCategoriaPrendas($idCategoria);
	$seccion->assign('url_img_categoria', $urlFoto); 
	$prendas = $interfaz->obtenerPrendasPorCategoria($idCategoria, $pagina);
	$cantResultados = $prendas["cantidad"];
	$totalPaginas = ceil($cantResultados/PRENDAS_POR_PAGINA);
	$seccion->assign('grillaPrendas', FuncionesSitio::generarHtmlPaginaPrendas($prendas["datos"]));
}
elseif($idColeccion > 0){
	$seccion->assign('nombreListado', "Prendas de la colección $nombreColeccion");
	$urlFoto = $interfaz->obtenerUrlFotoColeccionPrendas($idColeccion);
	$seccion->assign('url_img_categoria', $urlFoto); 
	$prendas = $interfaz->obtenerPrendasPorColeccion($idColeccion, $pagina);
	$cantResultados = $prendas["cantidad"];
	$totalPaginas = ceil($cantResultados/PRENDAS_POR_PAGINA);
	$seccion->assign('grillaPrendas', FuncionesSitio::generarHtmlPaginaPrendas($prendas["datos"]));
}

if($pagina > 0){
	$seccion->assign('ant_display', 'block');		
}
else{
	$seccion->assign('ant_display', 'none');	
}

if($pagina < ($totalPaginas-1)){
	$seccion->assign('sig_display', 'block');
}
else{
	$seccion->assign('sig_display', 'none');
}

$seccion->assign('pagina', $pagina);
$seccion->assign('idCategoria', $idCategoria);
$seccion->assign('idColeccion', $idColeccion);
$seccion->assign('LARGO_FOTO_CATEGORIA_PRENDA', LARGO_FOTO_CATEGORIA_PRENDA);
$seccion->assign('ANCHO_FOTO_CATEGORIA_PRENDA', ANCHO_FOTO_CATEGORIA_PRENDA);
$seccion->assign('LARGO_PREVIEW_PRENDA', LARGO_PREVIEW_PRENDA);
$seccion->assign('ANCHO_PREVIEW_PRENDA', ANCHO_PREVIEW_PRENDA);
$seccion->assign('imgFondo', FuncionesSitio::imagenFondoLinea($idLinea));

$marco->assign('contenido_seccion', $seccion->fetchHTML());
$marco->assign(FuncionesSitio::opcionLineaMenu($idLinea), 1);

$marco->printHTML();

?>