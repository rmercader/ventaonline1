<?PHP

include_once('../../app.config.php');
include(DIR_LIB.'nyiDATA.php');
include(DIR_BASE.'funciones-auxiliares.php');

// Conexion con la base de datos
$Cnx = nyiCNX();
$Cnx->debug = false;
include_once(DIR_BASE.'prendas/coleccion.class.php');

if(isset($_GET['id']) && is_numeric($_GET['id']) && intval($_GET['id']) > 0){
    header("Content-type: image/jpeg");
 	$obCat = new Coleccion($Cnx);
	$id = $_GET['id'];
	$foto = $obCat->obtenerFoto($id);
	ob_clean();
	echo $foto;
}

?>