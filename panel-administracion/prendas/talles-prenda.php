<?PHP

unset($_SESSION["id_prenda"]);
if(!isset($_GET['COD']) || !is_numeric($_GET['COD']) || intval($_GET['COD']) == 0){
	header("Location: admin-catalogo.php");	
	exit(0);
}

$idPrenda = $_GET['COD'];
$_SESSION["id_prenda"] = $idPrenda;

// Includes
include_once(DIR_BASE.'prendas/prenda.class.php');
include_once(DIR_BASE.'prendas/talle.class.php');

// Objeto
$objPrenda = new Prenda($Cnx);
$objTalle = new Talle($Cnx);

$mod_Contenido = '';
$error = "";
$html = new nyiHTML('prendas/talles-prenda.htm');
$idsTalles = $objTalle->getComboIds();

// Si viene con POST
if($_SERVER['REQUEST_METHOD'] == "POST"){
	$idsTallesAsociados = array();
	foreach($idsTalles as $idTalle){
		if(isset($_POST["talle_{$idTalle}"])){
			array_push($idsTallesAsociados, $idTalle);
		}
	}
	$res = $objPrenda->asociarTalles($idPrenda, $idsTallesAsociados);
	if($res != ""){
		$error = $res;	
	}
	else{
		$error = "Los talles fueron asociados correctamente.";
	}
}

$tallesTodos = $objPrenda->obtenerListaTallesParaEditar($idPrenda);
foreach($tallesTodos as $talle){
	array_push($idsTalles, $talle['id_talle']);
	$arrInfoTalle = array(
		'id_talle'=>$talle['id_talle'],
		'codigo'=>$talle['codigo'],
		'marcado'=>$talle['id_prenda'] == $idPrenda ? 'checked="checked"' : ''
	);
	$html->append('REG', $arrInfoTalle);
}

// Script Post
$html->assign('SCRIPT_POST', basename($_SERVER['SCRIPT_NAME']).$html->fetchParamURL($_GET));

// Cabezal
$Cab = new nyiHTML('base_cabezal_abm.htm');
$Cab->assign('NOMFORM', 'TALLES DE PRENDA');
$Cab->assign('NOMACCION', "Edición");
$Cab->assign('ACC', ACC_POST);
// Script Salir
$Cab->assign('SCRIPT_SALIR', "admin-catalogo.php");
$html->assign('NAVEGADOR', $Cab->fetchHTML());
$html->assign('id_prenda', $idPrenda);
$html->assign('error', $error);

$mod_Contenido = $html->fetchHTML();

?>