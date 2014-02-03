<?PHP

include('../../app.config.php');
include(DIR_LIB.'nyiDATA.php');
include(DIR_BASE.'funciones-auxiliares.php');

// Conexion con la base de datos
$Cnx = nyiCNX();
$Cnx->debug = false;
include(DIR_BASE.'prendas/color.class.php');

if(isset($_GET['id']) && is_numeric($_GET['id']) && intval($_GET['id']) > 0){
	$obColor = new Color($Cnx);
	$id = $_GET['id'];
	$thumb = intval($_GET['thumb']);
	header("Content-type: image/png");
	ob_clean();
	echo $obColor->obtenerImagen($id, ($thumb > 0 ? 'thumbnail' : 'imagen'));
}

?>