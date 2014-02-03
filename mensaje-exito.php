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
include(DIR_LIB.'nyiHTML.php');
$msjExito = new nyiHTML('msj-tiny-exito.htm');
$msjExito->assign('mensaje', $_SESSION["mensaje-exito"]);
unset($_SESSION["mensaje-exito"]);
$msjExito->printHTML();

?>