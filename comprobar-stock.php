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

$idPrenda = (int)$_GET['prenda'];
$idColor = (int)$_GET['color'];
$cantidad = (int)$_GET['cantidad'];
$codTalle = trim($_GET['talle']);

if($idPrenda > 0 && $idColor > 0 && $cantidad > 0 && $codTalle != ''){

	$interface = new Interfaz();
	$idTalle = $interface->obtenerIdTallePorCodigo($codTalle);
	$cantStock = $interface->obtenerCantidadStock($idPrenda, $idColor, $idTalle);

	if($cantStock >= $cantidad){
		echo 1;
	}
	else{
		echo 0;
	}
}
else {
	echo 0;
}

?>