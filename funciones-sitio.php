<?PHP

class FuncionesSitio {

	public static function obtenerVerificacionHMAC($var_name, $var_value, $var_code) {
		$encodingval = htmlspecialchars($var_code) . htmlspecialchars($var_name) . htmlspecialchars($var_value);
		return '||' . hash_hmac('sha256', $encodingval, API_KEY) . ($var_value == "--OPEN--" ? "||open" : "");
	}

	public static function generarHtmlPaginaPrendas($lstPrendas){
		$html = new nyiHTML('prendas-grilla.htm');
		$html->assign('lstPrendas', $lstPrendas);
		$html->assign('LARGO_PREVIEW_PRENDA', LARGO_PREVIEW_PRENDA);
		$html->assign('ANCHO_PREVIEW_PRENDA', ANCHO_PREVIEW_PRENDA);
		return $html->fetchHTML();
	}

	public static function generarMenu($idLinea){
		$interfaz = new Interfaz();
		$catPrincipales = $interfaz->obtenerCategoriasPrincipalesPorLinea($idLinea, array('id_categoria_prenda', 'nombre_categoria_prenda'));
		$html = "<ul>\r\n";
		foreach($catPrincipales as $cat){
			$html .= "<li><a href=\"prendas-categoria.php?categoria={$cat['id_categoria_prenda']}\">" . $cat['nombre_categoria_prenda'] . "</a></li>\r\n";
		}
		$html .= "</ul>\r\n";
		return $html;
	}

	public static function generarMenuCategorias($arrCategorias){
		if(is_array($arrCategorias)){
			$arrUI = array();
			foreach($arrCategorias as $itemSubCat){
				array_push($arrUI, array(
					'id'=>$itemSubCat['id_categoria_prenda'],
					'url'=>'prendas.php?categoria=' . $itemSubCat['id_categoria_prenda'],
					'nombre'=>$itemSubCat['nombre_categoria_prenda']
				));
			}
			$html = new nyiHTML('lista-subcategorias.htm');
			$html->assign('arrCats', $arrUI);
			return $html->fetchHTML();
		}
	}
	
	public static function generarMenuColecciones($arrColecciones){
		if(is_array($arrColecciones)){
			$arrUI = array();
			foreach($arrColecciones as $itemCol){
				array_push($arrUI, array(
					'id'=>$itemCol['id_coleccion'],
					'url'=>'prendas.php?coleccion=' . $itemCol['id_coleccion'],
					'nombre'=>$itemCol['nombre_coleccion']
				));
			}
			$html = new nyiHTML('lista-subcategorias.htm');
			$html->assign('arrCats', $arrUI);
			return $html->fetchHTML();
		}
	}
	
	public static function generarMenuParaPrendas($arrCategorias, $arrColecciones){
		$html = new nyiHTML('submenu-para-prendas.htm');
		$menu = FuncionesSitio::generarMenuCategorias($arrCategorias);
		$html->assign('subcategorias', $menu);
		$menu = FuncionesSitio::generarMenuColecciones($arrColecciones);
		$html->assign('colecciones', $menu);
		return $html->fetchHTML();
	}

	public static function opcionLineaMenu($idLinea){
		$menuActual = "";
		switch($idLinea){
			case LINEA_DAMA:
				$menuActual = 'damaActual';
				break;

			case LINEA_HOMBRE:
				$menuActual = 'hombreActual';
				break;
				
			case LINEA_INFANTIL:
				$menuActual = 'infantilActual';
				break;
		}
		return $menuActual;
	}
	
	public static function imagenFondoLinea($idLinea){
		$imagen = "";
		switch($idLinea){
			case LINEA_DAMA:
				$imagen = 'images/bg_damas.jpg';
				break;

			case LINEA_HOMBRE:
				$imagen = 'images/bg_hombres.jpg';
				break;
				
			case LINEA_INFANTIL:
				$imagen = 'images/bg_infantil.jpg';
				break;
			
			default:
				$imagen = 'images/bg_generales.jpg';
				break;
		}
		return $imagen;
	}
}

?>