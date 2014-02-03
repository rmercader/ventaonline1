<?PHP

unset($_SESSION["id_prenda"]);
if(!isset($_GET['COD']) || !is_numeric($_GET['COD']) || intval($_GET['COD']) == 0){
	header("Location: admin-catalogo.php");	
	exit(0);
}

$idPrenda = $_GET['COD'];
$_SESSION["id_prenda"] = $idPrenda;

// Includes
include(DIR_BASE.'prendas/prenda.class.php');

// Objeto
$objPrenda = new Prenda($Cnx, $xajax);

$mod_Contenido = '';
$error = "";
$html = new nyiHTML('prendas/colores-prenda.htm');

// Si viene con POST
if($_SERVER['REQUEST_METHOD'] == "POST"){
	if(intval($_POST["id_color"]) > 0){
		$res = $objPrenda->asociarNuevoColor($idPrenda, $_POST["id_color"]);
		if($res != ""){
			$error = $res;	
		}
	}
}

$html->assign('LARGO_THU_COLOR', LARGO_THU_COLOR);
$html->assign('ANCHO_THU_COLOR', ANCHO_THU_COLOR);
$html->assign('COLORES', $objPrenda->obtenerHtmlColoresAsociados($idPrenda));

// Script Post
$html->assign('SCRIPT_POST', basename($_SERVER['SCRIPT_NAME']).$html->fetchParamURL($_GET));

// Cabezal
$Cab = new nyiHTML('base_cabezal_abm.htm');
$Cab->assign('NOMFORM', 'COLORES DE PRENDA');
$Cab->assign('NOMACCION', "Edición");
$Cab->assign('ACC', ACC_VER);
// Script Salir
$Cab->assign('SCRIPT_SALIR', "admin-catalogo.php");
$html->assign('NAVEGADOR', $Cab->fetchHTML());
$html->assign('id_prenda', $idPrenda);
$html->assign('error', $error);
$xajax->setRequestURI(DIR_HTTP.'prendas/colores-prenda-ajax.php');
$xajax->registerFunction("eliminarAsociacionColor");
$xajax->registerFunction("buscarColoresPorNombreParaAgregar");

$mod_Contenido = $html->fetchHTML();

?>