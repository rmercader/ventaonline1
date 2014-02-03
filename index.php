<?php

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

$interfaz = new Interfaz();
$marco = new nyiHTML('masterpage.htm');

// Segun la orientacion de la home
$orientacion = $interfaz->obtenerOrientacionHome();
$templateHome = "";
switch($orientacion){
	case ORIENTACION_HORIZONTAL:
		$templateHome = "index.htm";
		break;
	case ORIENTACION_VERTICAL:
		$templateHome = "index-vertical.htm";
		break;
}
$seccion = new nyiHTML($templateHome);
$seccion->assign('LARGO_PREVIEW_PRENDA', LARGO_PREVIEW_PRENDA);
$seccion->assign('ANCHO_PREVIEW_PRENDA', ANCHO_PREVIEW_PRENDA);
$seccion->assign('lstDestacadas', $interfaz->obtenerPrendasDestacadas());
$seccion->assign('imgFondo', $interfaz->obtenerUrlImagenHome());

$marco->assign('contenido_seccion', $seccion->fetchHTML());
$marco->assign('homeActual', 1);
$marco->printHTML();
//LogEntero($marco->fetchHTML());

?>