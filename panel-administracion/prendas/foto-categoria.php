<?PHP

include('../../app.config.php');
include(DIR_LIB.'nyiDATA.php');
include(DIR_BASE.'funciones-auxiliares.php');

// Conexion con la base de datos
$Cnx = nyiCNX();
$Cnx->debug = false;

include(DIR_BASE.'prendas/categoria-prenda.class.php');

if(isset($_GET['id']) && is_numeric($_GET['id']) && intval($_GET['id']) > 0){
	$obCat = new CategoriaPrenda($Cnx);
	$id = $_GET['id'];
	header("Content-type: image/jpeg");
	ob_clean();
	echo $obCat->obtenerFoto($id);
}

?>