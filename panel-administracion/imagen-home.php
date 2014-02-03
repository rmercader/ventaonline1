<?PHP
// Evito CACHE
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

include('../app.config.php');
include(DIR_LIB.'nyiDATA.php');

// Conexion con la base de datos
$Cnx = nyiCNX();
$Cnx->debug = false;

header("Content-type: image/jpeg");
ob_clean();
echo $Cnx->getOne("SELECT imagen_home FROM configuracion");

?>