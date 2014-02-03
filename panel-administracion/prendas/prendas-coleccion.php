<?PHP

unset($_SESSION["id_coleccion"]);
if(!isset($_GET['COD']) || !is_numeric($_GET['COD']) || intval($_GET['COD']) == 0){
	header("Location: admin-catalogo.php");	
	exit(0);
}

$idColeccion = $_GET['COD'];
//$_SESSION["id_coleccion"] = $idColeccion;

// Includes
include(DIR_BASE.'prendas/coleccion.class.php');

// Objeto
$objColPrendas = new Coleccion($Cnx, $xajax);

$mod_Contenido = '';
$error = "";
$html = new nyiHTML('prendas/prendas-coleccion.htm');

// Si viene con POST
if($_SERVER['REQUEST_METHOD'] == "POST"){
	if(is_numeric($_POST["id_cuestion"]) && intval($_POST["id_cuestion"]) > 0){
		$msg = "";
		$res = "";
		$opcAsociacion = $_POST["opc_asociacion"];
		// Dependiendo del caso
		switch($opcAsociacion){
			case "prenda":
				$res = $objColPrendas->agregarPrenda($_POST["id_cuestion"], $idColeccion);
				break;

			case "categoria":
				$res = $objColPrendas->agregarPrendasCategoria($_POST["id_cuestion"], $idColeccion);
				break;
		}
		if($res != ""){
			$error .= $res;
		}
	}
	else{
		$error .= "Ingrese un elemento para asociar\n";
	}
}

$html->assign('PRENDAS', $objColPrendas->obtenerHtmlPrendasAsociadas($idColeccion));
$html->assign("OPC_IDS", array("prenda", "categoria"));
$html->assign("OPC_DSC", array("Agregar una prenda a la colección", "Agregar todas las prendas de una categoría a la colección"));
$html->assign("OPC_VAL", $_POST["opc_asociacion"]);

// Script Post
$html->assign('SCRIPT_POST', basename($_SERVER['SCRIPT_NAME']).$html->fetchParamURL($_GET));

// Cabezal
$Cab = new nyiHTML('base_cabezal_abm.htm');
$Cab->assign('NOMFORM', 'PRENDAS DE LA COLECCIÓN ' . strtoupper($objColPrendas->getNombre($idColeccion)));
$Cab->assign('NOMACCION', "Edición");
$Cab->assign('ACC', ACC_VER);
// Script Salir
$Parametros = $_GET;
unset($Parametros['ACC']);
unset($Parametros['COD']);
// Script Salir
$Cab->assign('SCRIPT_SALIR', basename($_SERVER['SCRIPT_NAME']).$Cab->fetchParamURL($Parametros));
$html->assign('NAVEGADOR', $Cab->fetchHTML());
$html->assign('id_coleccion', $idColeccion);
$html->assign('error', $error);
$xajax->setRequestURI(DIR_HTTP.'prendas/prendas-coleccion-ajax.php');
$xajax->registerFunction("eliminarAsociacionPrendas");

$mod_Contenido = $html->fetchHTML();

?>