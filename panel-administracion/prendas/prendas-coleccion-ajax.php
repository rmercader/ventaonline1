<?php

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
include(DIR_BASE.'prendas/coleccion.class.php');

function eliminarAsociacionPrendas($idColeccion, $idsPrendas){
	$objResponse = new xajaxResponse(); // Creo objeto Response
	$Cnx = nyiCNX(); // Creo la conexion
	$objCol = new Coleccion($Cnx);
	$res = $objCol->eliminarAsociacionPrendas($idColeccion, $idsPrendas);
	if($res != ""){
		$objResponse->alert($res);
	}
	$objResponse->assign("container", "innerHTML", $objCol->obtenerHtmlPrendasAsociadas($idColeccion));
	return $objResponse;
}

if(isset($_GET['term']) && isset($_GET['asociar']) && intval($_GET['id_coleccion']) > 0){
	$nomBuscado = $_GET['term'];
	$idColeccion = $_GET['id_coleccion'];
	$opcAsociacion = $_GET['asociar'];
	$objCol = new Coleccion($Cnx);
	
	// Dependiendo del caso
	switch($opcAsociacion){
		case "prenda":
			$resDropDown = $objCol->obtenerPrendasPorNombreParaAgregar($nomBuscado, $idColeccion);
			break;

		case "categoria":
			$objCat = new CategoriaPrenda($Cnx);
			$resDropDown = $objCat->obtenerCategoriasPorNombreConPrendas($nomBuscado, $objCol->getIdLinea($idColeccion));
		    break;
		
		default:
			$resDropDown = array();
			break;
	}
	
	$resultados = array();
	foreach($resDropDown as $item){
		array_push($resultados, array('label'=>$item['nombre'], 'value'=>"{$item['id']} - {$item['nombre']}"));
	}
	//LogArchivo(print_r($resDropDown, true));
	echo json_encode($resultados);
}
else{
	// Ajax
	$xajax->registerFunction("eliminarAsociacionPrendas");
	$xajax->processRequest();
	$xajax->printJavascript(DIR_XAJAX_PARA_ADMIN);
}
?>

