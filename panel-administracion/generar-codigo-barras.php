<?PHP

// Evito CACHE
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

include('../app.config.php');
include(DIR_BASE.'class/barcode39.class.php');

if(isset($_GET['codigo'])){
	$bc = new Barcode39($_GET['codigo']);
	$bc->draw();
}

?>