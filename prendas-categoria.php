<?PHP
// Evito CACHE
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Inicio Session
session_start();
ini_set('display_errors', 1);
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
$seccion = new nyiHTML('prendas-categoria.htm');
$idCategoria = 0;

if(isset($_GET['linea'])){
	$idLinea = intval($_GET['linea']);
	$nomLinea = $interfaz->obtenerNombreLinea($idLinea);
	$seccion->assign('nombre_linea', $nomLinea);
	/*
	$subCats = $interfaz->obtenerSubcategorias($idCategoria, array('id_categoria_prenda', 'nombre_categoria_prenda'));
	if(is_array($subCats)){
		$arrUI = array();
		foreach($subCats as $itemSubCat){
			array_push($arrUI, array(
				'id'=>$itemSubCat['id_categoria_prenda'],
				'url'=>'prendas-categoria.php?categoria=' . $itemSubCat['id_categoria_prenda'],
				'nombre'=>$itemSubCat['nombre_categoria_prenda']
			));
		}
		$seccion->assign('arr_cats', $arrUI);
	}
	
	// Prendas de la categoria, o de la primera de las hijas que encuentre
	$prendas = $interfaz->obtenerPrendasPorCategoria($idCategoria);
	if(isset($arrUI)){
		$i = 0;
		while(count($prendas) == 0 && $i < count($arrUI)){
			$prendas = $interfaz->obtenerPrendasPorCategoria($arrUI[$i]['id']);
			$i++;
		}
	}
	$seccion->assign('prendas', $prendas);*/
}

if(isset($_GET['categoria'])){
	$idCategoria = intval($_GET['categoria']);
	$categoria = $interfaz->obtenerDatosCategoria($idCategoria, array('nombre_categoria_prenda'));
	$seccion->assign('nombre_categoria', $categoria[0][nombre_categoria_prenda]);
	$subCats = $interfaz->obtenerSubcategorias($idCategoria, array('id_categoria_prenda', 'nombre_categoria_prenda'));
	if(is_array($subCats)){
		$arrUI = array();
		foreach($subCats as $itemSubCat){
			array_push($arrUI, array(
				'id'=>$itemSubCat['id_categoria_prenda'],
				'url'=>'prendas-categoria.php?categoria=' . $itemSubCat['id_categoria_prenda'],
				'nombre'=>$itemSubCat['nombre_categoria_prenda']
			));
		}
		$seccion->assign('arr_cats', $arrUI);
	}
	
	// Prendas de la categoria, o de la primera de las hijas que encuentre
	$prendas = $interfaz->obtenerPrendasPorCategoria($idCategoria);
	if(isset($arrUI)){
		$i = 0;
		while(count($prendas) == 0 && $i < count($arrUI)){
			$prendas = $interfaz->obtenerPrendasPorCategoria($arrUI[$i]['id']);
			$i++;
		}
	}
	$seccion->assign('prendas', $prendas);
}

$seccion->assign('LARGO_PREVIEW_PRENDA', LARGO_PREVIEW_PRENDA);
$seccion->assign('ANCHO_PREVIEW_PRENDA', ANCHO_PREVIEW_PRENDA);
$marco->assign('contenido_seccion', $seccion->fetchHTML());
$marco->assign('menuDama', generarMenu(LINEA_DAMA));
$marco->assign('menuHombre', generarMenu(LINEA_HOMBRE));
$marco->assign('menuInfantil', generarMenu(LINEA_INFANTIL));
$marco->printHTML();

?>