<?PHP

unset($_SESSION["id_prenda"]);
if(!isset($_GET['COD']) || !is_numeric($_GET['COD']) || intval($_GET['COD']) == 0){
	header("Location: admin-catalogo.php");	
	exit(0);
}

$idPrenda = $_GET['COD'];

// Includes
include_once(DIR_BASE.'prendas/prenda.class.php');
include_once(DIR_BASE.'prendas/color.class.php');
include_once(DIR_BASE.'prendas/talle.class.php');

// Objeto
$objPrenda = new Prenda($Cnx, $xajax);
$objColor = new Color($Cnx);
$objTalle = new Talle($Cnx);

$mod_Contenido = '';
$error = "";
$html = new nyiHTML('prendas/stock-prenda.htm');
$cantidad = 0;
$idColor = 0;
$idTalle = 0;

// Si viene con POST
if($_SERVER['REQUEST_METHOD'] == "POST"){
	$error = "";
	$idColor = intval($_POST['id_color']);
	$idTalle = intval($_POST['id_talle']);
	$cantidad = intval($_POST['cantidad']);
	
	if($idColor == 0){
		$error .= "No se ha seleccionado color.\n";
	}
	if($idTalle == 0){
		$error .= "No se ha seleccionado talle.\n";
	}
	if($error == ""){
		$res = $objPrenda->configurarStock($idPrenda, $idColor, $idTalle, $cantidad);
		if($res != ""){
			$error = $res;	
		}
	}
}

$coloresPrenda = iterator_to_array($objPrenda->obtenerColores($idPrenda));
for($i = 0; $i < count($coloresPrenda); $i++){
	$coloresPrenda[$i]["thumbnail"] = $objColor->getUrlImagen($coloresPrenda[$i]['id_color'], 1);
}
$html->assign("colores", $coloresPrenda);

$talles = $objPrenda->obtenerTallesAsociados($idPrenda);
foreach($talles as $talle){
	$html->append("ids_talles", $talle['id_talle']);
	$html->append("dsc_talles", $talle['codigo']);
}

$configStock = $objPrenda->obtenerConfiguracionStock($idPrenda);
for($i = 0; $i < count($configStock); $i++){
	$configStock[$i]["thumbnail"] = $objColor->getUrlImagen($configStock[$i]['id_color'], 1);
}
$html->assign("configuracion", $configStock);

// Script Post
$html->assign('SCRIPT_POST', basename($_SERVER['SCRIPT_NAME']).$html->fetchParamURL($_GET));

// Cabezal
$Cab = new nyiHTML('base_cabezal_abm.htm');
$Cab->assign('NOMFORM', 'STOCK DE PRENDA');
$Cab->assign('NOMACCION', "Edición");
$Cab->assign('ACC', ACC_VER);
// Script Salir
$Cab->assign('SCRIPT_SALIR', "admin-catalogo.php");
$html->assign('NAVEGADOR', $Cab->fetchHTML());
$html->assign('id_prenda', $idPrenda);
$html->assign('error', $error);
$html->assign("id_talle", $idTalle);
$html->assign("cantidad", $cantidad);

// Ajax
$xajax->setRequestURI(DIR_HTTP.'prendas/stock-prenda-ajax.php');
$xajax->registerFunction("obtenerCantidadStock");

$mod_Contenido = $html->fetchHTML();

?>