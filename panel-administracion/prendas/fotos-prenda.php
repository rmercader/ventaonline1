<?PHP

if(!isset($_GET['COD']) || !is_numeric($_GET['COD']) || intval($_GET['COD']) == 0){
	header("Location: admin-catalogo.php");	
	exit(0);
}

// Includes
include(DIR_BASE.'prendas/prenda.class.php');

// Objeto
$objPrenda = new Prenda($Cnx, $xajax);

$mod_Contenido = '';
$error = "";
$html = new nyiHTML('prendas/fotos-prenda.htm');

// Si viene con POST
if($_SERVER['REQUEST_METHOD'] == "POST"){
	if(isset($_POST["subefoto"]) && $_POST["subefoto"] == _SI && is_uploaded_file($_FILES["fotonueva"]["tmp_name"])){
		$res = $objPrenda->asociarNuevaFoto($_GET['COD'], $_FILES["fotonueva"]["tmp_name"], $_FILES["fotonueva"]["name"]);
		if($res != ""){
			$error = $res;
		}
	}
	elseif(isset($_POST["orden"]) && $_POST["orden"] != ""){
		$orden = explode(",", $_POST["orden"]);
		$nuevoOrden = array();
		foreach($orden as $item){
			array_push($nuevoOrden, trim($item));
		}
		$res = $objPrenda->ordenarFotos($_GET['COD'], $nuevoOrden);
		if($res != ""){
			$error = $res;
		}
		else{
			$error = "Las fotos fueron ordenadas correctamente.";
		}
	}
}

$galeria = $objPrenda->obtenerGaleriaFotos($_GET['COD']);
while(!$galeria->EOF){
	$extension = $galeria->fields['extension'];
	$nomSinExt = $galeria->fields['nombre_imagen']; 
	$html->append('FOTOS', array('nombre_imagen'=>$nomSinExt, 'url'=>DIR_HTTP_FOTOS_PRENDAS."{$_GET['COD']}/{$nomSinExt}-thu.{$extension}"));
	$galeria->MoveNext();
}

// Script Post
$html->assign('SCRIPT_POST', basename($_SERVER['SCRIPT_NAME']).$html->fetchParamURL($_GET));

// Cabezal
$Cab = new nyiHTML('base_cabezal_abm.htm');
$Cab->assign('NOMFORM', 'FOTOS DE LA PRENDA: ' . strtoupper($objPrenda->getNombre($_GET['COD'])));
$Cab->assign('NOMACCION', "Edición");
$Cab->assign('ACC', ACC_POST);
// Script Salir
$Cab->assign('SCRIPT_SALIR', "admin-catalogo.php");
$html->assign('NAVEGADOR', $Cab->fetchHTML());
$html->assign('LARGO_THUMBNAIL_PRENDA', LARGO_THUMBNAIL_PRENDA);
$html->assign('ANCHO_THUMBNAIL_PRENDA', ANCHO_THUMBNAIL_PRENDA);
$html->assign('id_prenda', $_GET['COD']);
$html->assign('ERROR', $error);
$xajax->setRequestURI(DIR_HTTP.'prendas/fotos-prenda-ajax.php');
$xajax->registerFunction("eliminarFoto");

$mod_Contenido = $html->fetchHTML();

?>