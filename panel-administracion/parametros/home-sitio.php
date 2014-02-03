<?php

include_once(DIR_BASE."class/interfaz.class.php");
$html = new nyiHTML('parametros/config-home-sitio.htm');
$interfaz = new Interfaz($Cnx);
$html->assign('o_values', array(ORIENTACION_HORIZONTAL, ORIENTACION_VERTICAL));
$html->assign('o_dsc', array('Horizontal', 'Vertical'));
$error = "";

if($_SERVER['REQUEST_METHOD'] == "POST"){
	if(is_uploaded_file($_FILES["imagen"]['tmp_name']) && $_FILES["imagen"]['size'] > 0){
		$error .= $interfaz->guardarImagenHome($_FILES['imagen']);
	}
	$orientacion_home = $_POST["orientacion_home"];
	$error .= $interfaz->guardarOrientacionHome($orientacion_home);
	if($error == ""){
		$error = "La configuración se ha guardado correctamente.";
	}
}
else {
	$orientacion_home = $interfaz->obtenerOrientacionHome();
}

$html->assign('error', $error);
$html->assign('orientacion_home', $orientacion_home);
$html->assign('urlImagen', $interfaz->obtenerUrlImagenHome());
// Cabezal
$Cab = new nyiHTML('base_cabezal_abm.htm');
$Cab->assign('NOMFORM', 'ADMINISTRAR PÁGINA HOME DEL SITIO');
$Cab->assign('NOMACCION', getNomAccion(ACC_POST));
$Cab->assign('ACC', ACC_POST);
$Cab->assign('SCRIPT_SALIR', basename($_SERVER['SCRIPT_NAME']));
$html->assign('NAVEGADOR', $Cab->fetchHTML());
$mod_Contenido = $html->fetchHTML();

?>