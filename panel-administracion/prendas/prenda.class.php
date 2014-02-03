<?PHP
// includes
include_once(DIR_BASE.'class/table.class.php');
include_once(DIR_BASE.'seguridad/usuario.class.php');
include_once(DIR_BASE.'prendas/categoria-prenda.class.php');
include_once(DIR_BASE.'fckeditor/fckeditor.php');
include_once(DIR_BASE.'class/image-handler.class.php');

class Prenda extends Table {

	var $TamTextoGrilla = 200;
	var $Ajax;
	var $TablaImg;
	var $ValoresImg;
	
	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function Prenda($DB, $AJAX=''){
		// Conexion
		$this->Table($DB, 'prenda');
		$this->TablaImg   = new Table($DB, 'prenda_foto');
		$this->AccionesGrid = array(ACC_BAJA, ACC_MODIFICACION, ACC_CONSULTA);
		// Ajax
		$this->Ajax = $AJAX;
	}
	
	function SetSoloLectura(){
		$this->AccionesGrid = array(ACC_CONSULTA);
	}
	
	function asociarNuevaFoto($idPrenda, $fileTmpName, $fileName){
		$errores = "";
		if(!file_exists(DIR_FOTOS_PRENDAS."{$idPrenda}")){
			mkdir(DIR_FOTOS_PRENDAS."{$idPrenda}");
			chmod(DIR_FOTOS_PRENDAS."{$idPrenda}", 0755);
		}
		
		$extension = GetExtension($fileName);
		$this->StartTransaction();
		$index = $this->DB->getOne("SELECT MAX(orden) FROM prenda_foto WHERE id_prenda = $idPrenda");
		$index++;
		$nuevoNombre = HTMLize(str_ireplace(".{$extension}", "", $fileName));
		
		$ImgHandler = new ImageHandler();
		// Imagen Thumbnail
		if ($ImgHandler->open_image($fileTmpName) == 0){
			// Ajusta la imagen si es necesario
			$ImgHandler->resize_image(LARGO_THUMBNAIL_PRENDA, ANCHO_THUMBNAIL_PRENDA);
			// La guarda
			$rutaImgThu = DIR_FOTOS_PRENDAS."{$idPrenda}/{$nuevoNombre}-thu.{$extension}";
			$ImgHandler->image_to_file($rutaImgThu);
		}
		else{
			$errores .= "No se pudo guardar la imagen thumbnail.\n";
		}
		// Imagen Preview
		if ($ImgHandler->open_image($fileTmpName) == 0){
			// Ajusta la imagen si es necesario
			$ImgHandler->resize_image(LARGO_PREVIEW_PRENDA, ANCHO_PREVIEW_PRENDA);
			// La guarda
			$rutaImgPrv = DIR_FOTOS_PRENDAS."{$idPrenda}/{$nuevoNombre}-prv.{$extension}";
			$ImgHandler->image_to_file($rutaImgPrv);
		}
		else{
			$errores .= "No se pudo guardar la imagen preview.\n";
		}
		// Imagen Detalle
		if ($ImgHandler->open_image($fileTmpName) == 0){
			// Ajusta la imagen si es necesario
			$ImgHandler->resize_image(LARGO_FOTO_PRENDA, ANCHO_FOTO_PRENDA);
			// La guarda
			$rutaImg = DIR_FOTOS_PRENDAS."{$idPrenda}/{$nuevoNombre}.{$extension}";
			$ImgHandler->image_to_file($rutaImg);
		}
		else{
			$errores .= "No se pudo guardar la imagen detalle.\n";
		}
		
		$this->DB->execute("INSERT INTO prenda_foto(id_prenda, nombre_imagen, extension, orden) VALUES ($idPrenda, '{$nuevoNombre}', '{$extension}', $index)");
		$this->CompleteTransaction();
		if($this->DB->ErrorMsg() != ""){
			$errores .= "Ocurrio un error al salvar la imagen.\n";
			LogError("Ocurrio un error al salvar la imagen {$nuevoNombre}.{$extension} de prenda $idPrenda a la base de datos.\n" . $this->DB->ErrorMsg(), basename(__FILE__), "asociarNuevaFoto($idPrenda, $fileTmpName, $fileName)");
		}
		return $errores;
	}
	
	function obtenerGaleriaFotos($idPrenda){
		return $this->DB->execute("SELECT * FROM prenda_foto WHERE id_prenda = {$idPrenda} ORDER BY orden");
	}
	
	function eliminarFoto($idPrenda, $nombre){
		$ext = $this->DB->getOne("SELECT extension FROM prenda_foto WHERE id_prenda = {$idPrenda} AND nombre_imagen = '{$nombre}'");
		
		$rutaImgThu = DIR_FOTOS_PRENDAS."{$idPrenda}/{$nombre}-thu.{$ext}";
		if(file_exists($rutaImgThu)){
			@unlink($rutaImgThu);
		}
		
		$rutaImgPrv = DIR_FOTOS_PRENDAS."{$idPrenda}/{$nombre}-prv.{$ext}";
		if(file_exists($rutaImgPrv)){
			@unlink($rutaImgPrv);
		}
		
		$rutaImg = DIR_FOTOS_PRENDAS."{$idPrenda}/{$nombre}.{$ext}";
		if(file_exists($rutaImg)){
			@unlink($rutaImg);
		}
		
		$this->StartTransaction();
		$orden = $this->DB->getOne("SELECT orden FROM prenda_foto WHERE id_prenda = $idPrenda AND nombre_imagen = '{$nombre}'");
		$this->DB->execute("DELETE FROM prenda_foto WHERE id_prenda = {$idPrenda} AND nombre_imagen = '{$nombre}'");
		$this->DB->execute("UPDATE prenda_foto SET orden = (orden-1) WHERE orden > {$orden} AND id_prenda = $idPrenda");
		$this->CompleteTransaction();
	}
	
	function ordenarFotos($idPrenda, $nuevoOrden){
		$errores = "";
		$i = 1;
		$this->StartTransaction();
		foreach($nuevoOrden as $nombre_imagen){
			$this->DB->execute("UPDATE prenda_foto SET orden = {$i} WHERE nombre_imagen = '{$nombre_imagen}' AND id_prenda = $idPrenda");
			$i++;
		}
		$this->CompleteTransaction();
		if($this->DB->ErrorMsg() != ""){
			$errores .= "Ocurrio un error al ordenar las fotos.\n";
			LogError("Ocurrio un error al ordenar las imagenes de prenda $idPrenda.\n" . $this->DB->ErrorMsg(), basename(__FILE__), "ordenarFotos($idPrenda, $nuevoOrden)");
		}
		return $errores;
	}
	
	function asociarNuevoColor($idPrenda, $idColor){
		$Ok = $this->DB->execute("INSERT INTO prenda_color(id_prenda, id_color) VALUES ($idPrenda, $idColor)");
		if($Ok === false){
			LogError($this->DB->ErrorMsg(), __FILE__, "asociarNuevoColor($idPrenda, $idColor)");
			return "Ocurrió un error al asociar el color.";
		}
		else{
			return "";
		}
	}
	
	function eliminarAsociacionColor($idPrenda, $idColor){
		$this->StartTransaction();
		$this->DB->execute("DELETE FROM prenda_color WHERE id_prenda = $idPrenda AND id_color = $idColor");
		$this->DB->execute("DELETE FROM prenda_stock WHERE id_prenda = $idPrenda AND id_color = $idColor");
		$this->CompleteTransaction();
		if($this->DB->ErrorMsg() != ""){
			$errores .= "Ocurrió un error al eliminar el color.\n";
			LogError($this->DB->ErrorMsg(), __FILE__, "eliminarAsociacionColor($idPrenda, $idColor)");
		}
		return $errores;
	}
	
	function obtenerColores($idPrenda){
		return $this->DB->execute("SELECT c.id_color, c.nombre_color FROM color c INNER JOIN prenda_color pc ON pc.id_color = c.id_color AND pc.id_prenda = {$idPrenda} ORDER BY c.nombre_color");
	}
	
	function obtenerHtmlColoresAsociados($idPrenda){
		// Obtengo todos los datos de los colores asociados a la prenda
		$listaColores = $this->obtenerColores($idPrenda);
		$html = new nyiHTML('prendas/lista-colores-prenda.htm');
		$html->assign('LARGO_IMG_COLOR', LARGO_IMG_COLOR);
		$html->assign('ANCHO_IMG_COLOR', ANCHO_IMG_COLOR);
		
		// Comienzo a agregar items
		while(!$listaColores->EOF){
			$idColor = $listaColores->fields['id_color'];
			$nombre = $listaColores->fields['nombre_color'];
			$html->append('REG', array('id_color'=>$idColor, 'nombre_color'=>$nombre, 'url_imagen_color'=>DIR_HTTP . "prendas/foto-color.php?thumb=0&id=$idColor"));
			$listaColores->MoveNext();
		}
		return $html->fetchHTML();
	}
	
	function obtenerColoresPorNombreParaAgregar($nomBuscarColor, $idPrenda){
		$query  = "SELECT c.id_color, c.nombre_color ";
		$query .= "FROM color c ";
		$query .= "WHERE c.nombre_color LIKE '%{$nomBuscarColor}%' ";
		$query .= "AND NOT EXISTS(SELECT pc.id_prenda FROM prenda_color pc WHERE pc.id_prenda = $idPrenda AND pc.id_color = c.id_color) ";
		$query .= "ORDER BY c.nombre_color";
		
		return $this->DB->execute($query);
	}
	
	function asociarTalles($idPrenda, $idsTallesAsociados){
		$this->StartTransaction();
		$this->DB->execute("DELETE FROM prenda_talle WHERE id_prenda = {$idPrenda}");
		foreach($idsTallesAsociados as $idTalle){
			$q = "INSERT INTO prenda_talle(id_talle, id_prenda) VALUES($idTalle, $idPrenda)";
			$this->DB->execute($q);
		}
		$q = "DELETE FROM prenda_stock WHERE id_prenda = {$idPrenda} AND NOT EXISTS(SELECT * FROM prenda_talle pt WHERE pt.id_talle = prenda_stock.id_talle AND prenda_stock.id_prenda = {$idPrenda})";
		$this->DB->execute($q);
		$this->CompleteTransaction();
		if($this->DB->ErrorMsg() != ""){
			$errores .= "Ocurrio un error al asociar los talles.\n";
			LogError("Ocurrio un error al asociar los talles.\n" . $this->DB->ErrorMsg(), basename(__FILE__), "asociarTalles($idPrenda, $idsTallesAsociados)");
		}
		return $errores;
	}
	
	// ------------------------------------------------
	// Prepara datos para Grid y PDF's
	// ------------------------------------------------
	function _Registros($Regs=0){
		// Creo grid
		$Grid  = new nyiGridDB('prendaS', $Regs, 'base_grid.htm');
		
		// Configuro
		$Grid->setParametros(isset($_GET['PVEZ']), 'nombre_prenda');
		$Grid->setPaginador('base_navegador.htm');
		$arrCriterios = array(
			'p.id_prenda'=>'Identificador',
			'nombre_categoria_prenda'=>'Categoria', 
			'l.nombre_linea'=>'Linea', 
			'nombre_prenda'=>'Nombre', 
			"IF(p.destacada, 'Si', 'No')"=>"Destacada",
			"IF(p.visible, 'Si', 'No')"=>"Visible",
			'p.precio'=>"Precio"
		);
		$Grid->setFrmCriterio('base_criterios_buscador.htm', $arrCriterios);
	
		// Si viene con post
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			$Grid->setCriterio($_POST['ORDEN_CAMPO'], $_POST['ORDEN_TXT'], $_POST['CBPAGINA']);
			unset($_GET['NROPAG']);
		}
		
		// Numero de Pagina
		if (isset($_GET['NROPAG'])){
			$Grid->setPaginaAct($_GET['NROPAG']);
		}
	
		$Campos = "p.id_prenda AS id, c.nombre_categoria_prenda, p.nombre_prenda, pf.nombre_imagen, pf.extension, l.nombre_linea, IF(p.destacada, 'Si', 'No') AS destacada, IF(p.visible, 'Si', 'No') AS visible, p.precio";
		$From = "prenda p INNER JOIN categoria_prenda c ON c.id_categoria_prenda = p.id_categoria_prenda LEFT OUTER JOIN prenda_foto pf ON pf.id_prenda = p.id_prenda AND pf.orden = 1 INNER JOIN linea l ON l.id_linea = c.id_linea";
		
		$Grid->getDatos($this->DB, $Campos, $From);
		
		// Devuelvo
		return($Grid);
	}

	// ------------------------------------------------
	// Genera Formulario
	// ------------------------------------------------
	function _Frm($Accion){
		// Conexion
		$Cnx = $this->DB;
		$id = $this->Registro['id_prenda'];
		$id_aux = $id == "" ? 0 : $id;
		
		// Formulario
		$Form = new nyiHTML('prendas/prenda-frm.htm');
		$Form->assign('ACC', $Accion);		
		
		// Datos
		$Form->assign('id_prenda', $id);
		$Form->assign('id_categoria_prenda', $this->Registro['id_categoria_prenda']);
		$Form->assign('nombre_prenda', $this->Registro['nombre_prenda']);
		$Form->assign('precio', $this->Registro['precio']);
		$Form->assign('visible', $this->Registro['visible'] == 1 ? 'checked="checked"' : '');
		$Form->assign('destacada', $this->Registro['destacada'] == 1 ? 'checked="checked"' : '');
		
		// Tengo que meterlo como caja de texto enriquecido
		$editor = new FCKeditor('DESCRIPCION') ;
		$editor->BasePath = 'fckeditor/' ;
		$editor->Height = ALTURA_EDITOR;
		$editor->Config['EnterMode'] = 'br';
		$editor->Value = $this->Registro['descripcion'];
		$contenido = $editor->CreateHtml();
		$Form->assign('DESCRIPCION', $contenido);
		
		$editor = new FCKeditor('INFOTECNICA') ;
		$editor->BasePath = 'fckeditor/' ;
		$editor->Height = ALTURA_EDITOR;
		$editor->Config['EnterMode'] = 'br';
		$editor->Value = $this->Registro['info_tecnica'];
		$contenido = $editor->CreateHtml();
		$Form->assign('INFOTECNICA', $contenido);
		
		$TblCat = new Categoriaprenda($Cnx);
		$Form->assign('categoria_prenda_id', $TblCat->GetComboIdsParaprenda());
		$Form->assign('categoria_prenda_nom', $TblCat->GetComboNombresParaprenda());
		
		if($Accion != ACC_ALTA && $Accion != ACC_MODIFICACION){
			// Si es una baja o consulta, no dejar editar
			$Form->assign('SOLO_LECTURA', 'readonly');
			$Form->assign('categoria_prenda_txt', $TblCat->nombre($this->Registro['id_categoria_prenda']));
		}
		
		if(isset($_GET["REDIRIGIR"])){
			unset($_GET["REDIRIGIR"]);
			$this->informarSalvarOk();
		}
		
		// Script Post
		$Form->assign('SCRIPT_POST',basename($_SERVER['SCRIPT_NAME']).$Form->fetchParamURL($_GET));
	
		// Cabezal
		$Cab = new nyiHTML('base_cabezal_abm.htm');
		$Cab->assign('NOMFORM', 'PRENDAS');
		$Cab->assign('NOMACCION', getNomAccion($Accion));
		$Cab->assign('ACC', $Accion);
		
		// Script Listado
		$Parametros = $_GET;
		unset($Parametros['ACC']);
		unset($Parametros['COD']);
		$Cab->assign('SCRIPT_LIS', basename($_SERVER['SCRIPT_NAME']).$Cab->fetchParamURL($Parametros));
		// Script Salir
		$Cab->assign('SCRIPT_SALIR', basename($_SERVER['SCRIPT_NAME']).$Cab->fetchParamURL($Parametros));
		$Form->assign('NAVEGADOR', $Cab->fetchHTML());
		$Form->assign('ERROR', $this->Error);
	
		// Contenido
		return($Form->fetchHTML());
	}
	
	function getExtensionImagen($id, $i){
		return $this->DB->getOne("SELECT extension_imagen FROM prenda_foto WHERE id_prenda = $id AND numero_imagen = $i");
	}

	// ------------------------------------------------
	// Cargo campos desde la base de datos
	// ------------------------------------------------
	function _GetDB($Cod=-1,$Campo='id_prenda'){
		// Cargo campos
		$this->Registro[$Campo] = $Cod;
		$this->TablaDB->getRegistro($this->Registro, $Campo);
	}
	
	function getDescripcion($id_prenda){
		return $this->DB->getOne("SELECT descripcion FROM prenda WHERE id_prenda = $id_prenda");
	}
	
	function getInfoTecnica($id_prenda){
		return $this->DB->getOne("SELECT info_tecnica FROM prenda WHERE id_prenda = $id_prenda");
	}
	
	function getNombre($id_prenda){
		return $this->DB->getOne("SELECT nombre_prenda FROM prenda WHERE id_prenda = $id_prenda");
	}
	
	function getPrecio($id_prenda){
		return $this->DB->getOne("SELECT precio FROM prenda WHERE id_prenda = $id_prenda");
	}
	
	// ------------------------------------------------
	// Cargo campos desde el formulario
	// ------------------------------------------------
	function _GetFrm(){
		// Cargo desde el formulario
		$this->Registro['id_prenda'] = $_POST['id_prenda'];
		$this->Registro['id_categoria_prenda'] = $_POST['id_categoria_prenda'];
		$this->Registro['nombre_prenda'] = $_POST['nombre_prenda'];
		$this->Registro['descripcion'] = stripslashes($_POST['DESCRIPCION']);
		$this->Registro['info_tecnica'] = stripslashes($_POST['INFOTECNICA']);
		$this->Registro['precio'] = number2db($_POST['precio']);
		$this->Registro['visible'] = $_POST['visible'] ? 1 : 0;
		$this->Registro['destacada'] = $_POST['destacada'] ? 1 : 0;
	}
	
	// ------------------------------------------------
	// Devuelve html de la Grid
	// ------------------------------------------------
	function grid($Regs){
		// Datos
		$Grid = $this->_Registros($Regs);
		$Grid->addVariable('TAM_TXT', $this->TamTextoGrilla);
		//$Grid->addVariable('DIR_HTTP_FOTOS_PRENDAS', DIR_HTTP_FOTOS_PRENDAS);
		
		// devuelvo
		return ($Grid->fetchGrid('prendas/prenda-grid.htm', 'Listado de prendas',
								basename($_SERVER['SCRIPT_NAME']), // Paginador
								"", // PDF
								basename($_SERVER['SCRIPT_NAME']), // Home
								basename($_SERVER['SCRIPT_NAME']), // Mto
								$this->AccionesGrid));
	}
	
	function getLastId(){
		return $this->DB->getOne("SELECT max(id_prenda) FROM prenda");
	}
	
	function afterDelete($id){
		$dir = DIR_FOTOS_PRENDAS.$id;
		BorrarDirectorio($dir);
	}
	
	function afterInsert($id){
		$this->Registro['id_prenda'] = $id;
	}
	
	function afterEdit(){
		$this->informarSalvarOk();
	}
	
	function informarSalvarOk(){
		$this->Error = "Los datos de la prenda se han guardado correctamente.";
	}
	
	function ObtenerPrendasPorCategoria($id_categoria_prenda, $visible=''){
		if($visible === true){
			$sqlVisibles = "visible = 1 AND";
		}
		else if($visible === false){
			$sqlVisibles = "visible = 0 AND";
		}
	
		$sql = "SELECT id_prenda, nombre_prenda FROM prenda WHERE $sqlVisibles id_categoria_prenda = $id_categoria_prenda ORDER BY ordinal";
		
		return $this->DB->execute($sql);
	}
	
	// Mas simple
	function ObtenerPrendasCategoria($id_categoria_prenda, $arrayAsociativo=false){
		$sql = "SELECT id_prenda, nombre_prenda FROM prenda WHERE id_categoria_prenda = $id_categoria_prenda AND visible = 1 ORDER BY nombre_prenda";
		$resultados = $this->DB->execute($sql);
		$datos = $resultados;
		if($arrayAsociativo){
			$datos = array();
			while(!$resultados->EOF){
				array_push($datos, array(
					'id_prenda'=>$resultados->fields['id_prenda'], 
					'nombre_prenda'=>$resultados->fields['nombre_prenda'],
					'src_imagen_thu'=>$this->GetURLImagenThumbnail($resultados->fields['id_prenda']),
					'src_imagen_thu_local'=>$this->GetURLImagenThumbnailLocal($resultados->fields['id_prenda'])
				));
				$resultados->MoveNext();
			}
		}
		return $datos;
	}
	
	function SetOrdinal($id, $valOrdinal){
		$sql = "UPDATE prenda SET ordinal = $valOrdinal WHERE id_prenda = $id";
		$OK = $this->DB->execute($sql);
		if($OK === false){
			$this->Error = $this->DB->ErrorMsg();
		}
	}
	
	function obtenerDatos($idPrenda){
		$q =  "SELECT p.id_prenda, p.id_categoria_prenda, p.nombre_prenda, p.descripcion, p.precio, ";
		$q .= "CONCAT('" . DIR_HTTP_FOTOS_PRENDAS . "', CONCAT(p.id_prenda, CONCAT('/', CONCAT(pf.nombre_imagen, CONCAT('-thu.', pf.extension))))) AS url_thumbnail, ";
		$q .= "CONCAT('" . DIR_HTTP_FOTOS_PRENDAS . "', CONCAT(p.id_prenda, CONCAT('/', CONCAT(pf.nombre_imagen, CONCAT('.', pf.extension))))) AS url_imagen ";
		$q .= "FROM prenda p INNER JOIN prenda_foto pf ON pf.id_prenda = p.id_prenda AND pf.orden = 1 WHERE p.id_prenda = $idPrenda";
		
		return iterator_to_array($this->DB->execute($q));
	}
	
	function obtenerListaTallesParaEditar($idPrenda){
		$q = "SELECT t.id_talle, t.codigo, p.id_prenda FROM talle t LEFT OUTER JOIN prenda_talle p ON p.id_talle = t.id_talle AND p.id_prenda = $idPrenda ORDER BY t.orden_codigo";
		$res = $this->DB->execute($q);
		return iterator_to_array($res);
	}
	
	function obtenerListadoDestacadas(){
		$q =  "SELECT p.id_prenda, p.nombre_prenda, p.precio, CONCAT('" . DIR_HTTP_FOTOS_PRENDAS . "', CONCAT(p.id_prenda, CONCAT('/', CONCAT(pf.nombre_imagen, CONCAT('-prv.', pf.extension))))) AS url_preview ";
		$q .= "FROM prenda p INNER JOIN prenda_foto pf ON pf.id_prenda = p.id_prenda AND pf.orden = 1 AND p.visible = 1 AND p.destacada = 1 ";
		$q .= "ORDER BY p.ordinal, p.nombre_prenda LIMIT " . PRENDAS_POR_PAGINA;
		$res = $this->DB->execute($q);
		return iterator_to_array($res);
	}
	
	function obtenerConfiguracionStock($idPrenda){
		$q =  "SELECT s.id_color, c.nombre_color, s.id_talle, t.codigo, s.cantidad ";
		$q .= "FROM prenda_stock s INNER JOIN color c ON s.id_color = c.id_color ";
		$q .= "INNER JOIN talle t ON s.id_talle = t.id_talle ";
		$q .= "WHERE s.id_prenda = $idPrenda ";
		$q .= "ORDER BY c.nombre_color, t.orden_codigo";
		$res = $this->DB->execute($q);
		return iterator_to_array($res);
	}
	
	function obtenerTallesAsociados($idPrenda){
		$q = "SELECT t.id_talle, t.codigo FROM talle t INNER JOIN prenda_talle p ON p.id_talle = t.id_talle AND p.id_prenda = $idPrenda ORDER BY t.orden_codigo";
		$res = $this->DB->execute($q);
		return iterator_to_array($res);
	}
	
	function configurarStock($idPrenda, $idColor, $idTalle, $cantidad){
		$this->StartTransaction();
		$this->DB->execute("DELETE FROM prenda_stock WHERE id_prenda = $idPrenda AND id_color = $idColor AND id_talle = $idTalle");
		$this->DB->execute("INSERT INTO prenda_stock(id_prenda, id_color, id_talle, cantidad) VALUES ($idPrenda, $idColor, $idTalle, $cantidad)");
		$this->CompleteTransaction();
		if($this->DB->ErrorMsg() != ""){
			$errores .= "Ocurrio un error al configurar el stock.\n";
			LogError("Ocurrio un error al configurar el stock.\n" . $this->DB->ErrorMsg(), basename(__FILE__), "configurarStock($idPrenda, $idColor, $idTalle, $cantidad)");
		}
		return $errores;
	}
	
	function obtenerCantidadStock($idPrenda, $idColor, $idTalle){
		$q = "SELECT cantidad FROM prenda_stock WHERE id_prenda = $idPrenda AND id_color = $idColor AND id_talle = $idTalle";
		return intval($this->DB->getOne($q));
	}
	
	function obtenerListadoPorCategoria($idCategoria, $offSet){
		$cols = "p.id_prenda, p.nombre_prenda, p.precio, CONCAT('" . DIR_HTTP_FOTOS_PRENDAS . "', CONCAT(p.id_prenda, CONCAT('/', CONCAT(pf.nombre_imagen, CONCAT('-prv.', pf.extension))))) AS url_preview";
		$q = "FROM prenda p INNER JOIN prenda_foto pf ON pf.id_prenda = p.id_prenda AND pf.orden = 1 AND p.visible = 1 AND p.id_categoria_prenda = $idCategoria";
		
		$qCount = "SELECT COUNT(p.id_prenda) $q";
		$totalrows = $this->DB->getOne($qCount);
		
		$qSelect = "SELECT $cols $q ORDER BY p.destacada, p.ordinal, p.nombre_prenda LIMIT $offSet, " . PRENDAS_POR_PAGINA;
		$res = $this->DB->execute($qSelect);
		return array("datos"=>iterator_to_array($res), "cantidad"=>$totalrows);
	}
	
	function obtenerListadoPorColeccion($idColeccion, $offSet){
		$cols = "p.id_prenda, p.nombre_prenda, p.precio, CONCAT('" . DIR_HTTP_FOTOS_PRENDAS . "', CONCAT(p.id_prenda, CONCAT('/', CONCAT(pf.nombre_imagen, CONCAT('-prv.', pf.extension))))) AS url_preview";
		$q .= "FROM prenda p INNER JOIN prenda_foto pf ON pf.id_prenda = p.id_prenda AND pf.orden = 1 AND p.visible = 1 ";
		$q .= "WHERE EXISTS(SELECT c.id_prenda FROM coleccion_prenda c WHERE c.id_coleccion = $idColeccion AND c.id_prenda = p.id_prenda)";
		
		$qCount =  "SELECT COUNT(p.id_prenda) $q";
		$totalrows = $this->DB->getOne($qCount);
		
		$qSelect = "SELECT $cols $q ORDER BY p.destacada, p.ordinal, p.nombre_prenda LIMIT $offSet, " . PRENDAS_POR_PAGINA;
		//echo "<pre>$qSelect</pre>\n";
		$res = $this->DB->execute($qSelect);
		return array("datos"=>iterator_to_array($res), "cantidad"=>$totalrows);
	}
}
?>