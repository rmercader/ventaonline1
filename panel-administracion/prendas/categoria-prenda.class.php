<?PHP

// includes
include_once(DIR_BASE.'class/table.class.php');
include_once(DIR_BASE.'class/image-handler.class.php');
include_once(DIR_BASE.'prendas/linea-prenda.class.php');

class CategoriaPrenda extends Table {

	private $imgContent = '';

	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function CategoriaPrenda($DB, $AJAX=''){
		// Conexion
		$this->Table($DB, 'categoria_prenda');
		$this->AccionesGrid = array(ACC_BAJA, ACC_MODIFICACION, ACC_CONSULTA);
		// Ajax
		$this->Ajax = $AJAX;
	}
	
	function SetSoloLectura(){
		$this->AccionesGrid = array(ACC_CONSULTA);
	}
	
	// ------------------------------------------------
	// Prepara datos para Grid y PDF's
	// ------------------------------------------------
	function _Registros($Regs=0){
		// Creo grid
		$Grid  = new nyiGridDB('ADMINISTRAR CATEGORIAS DE PRENDAS', $Regs, 'base_grid.htm');
		
		// Configuro
		$Grid->setParametros(isset($_GET['PVEZ']), 'c.nombre_categoria_prenda'); // Parametros de la sesion
		$Grid->setPaginador('base_navegador.htm');
		$Grid->setFrmCriterio('base_criterios_buscador.htm', array('c.nombre_categoria_prenda'=>'Nombre', 'l.nombre_linea'=>'Linea', "IF(c.destacada, 'Si', 'No')"=>"Destacada", 'p.nombre_categoria_prenda'=>'Categoria padre'));
	
		// Si viene con post
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			$Grid->setCriterio($_POST['ORDEN_CAMPO'],$_POST['ORDEN_TXT'],$_POST['CBPAGINA']);
			unset($_GET['NROPAG']);
		}
		
		// Numero de Pagina
		if (isset($_GET['NROPAG'])){
			$Grid->setPaginaAct($_GET['NROPAG']);
		}
			
		$campos = "c.id_categoria_prenda AS id, c.nombre_categoria_prenda, IF(c.destacada, 'Si', 'No') AS destacada_dsc, p.nombre_categoria_prenda AS nombre_categoria_padre, l.nombre_linea";
		$from = "categoria_prenda c LEFT OUTER JOIN categoria_prenda p ON c.id_categoria_padre = p.id_categoria_prenda INNER JOIN linea l ON l.id_linea = c.id_linea";
			
		$Grid->getDatos($this->DB, $campos, $from);
		
		// Devuelvo
		return($Grid);
	}

	function obtenerCategoriasConPrendas($visibles=''){
		if($visibles === true){
			$sqlVisibles = "p.visible = 1 AND";
		}
		else if($visibles === false){
			$sqlVisibles = "p.visible = 0 AND";
		}
	
		return $this->DB->execute("SELECT * FROM categoria_prenda c WHERE EXISTS (SELECT id_prenda FROM prenda p WHERE $sqlVisibles p.id_categoria_prenda = c.id_categoria_prenda)");
	}
	
	function obtenerCategoriasPorNombreConPrendas($nomBuscado, $idLinea=0){
		$q =  "SELECT c.id_categoria_prenda AS id, c.nombre_categoria_prenda AS nombre ";
		$q .= "FROM categoria_prenda c ";
		$q .= "WHERE c.nombre_categoria_prenda LIKE '%$nomBuscado%' AND ";
		$q .= "EXISTS (SELECT id_prenda FROM prenda p WHERE p.id_categoria_prenda = c.id_categoria_prenda)";
		if($idLinea > 0){
			$q .= " AND c.id_linea = $idLinea";
		}
		return iterator_to_array($this->DB->execute($q));
	}
	
	function nombre($idCategoria){
		return $this->DB->getOne("SELECT nombre_categoria_prenda FROM categoria_prenda WHERE id_categoria_prenda = {$idCategoria}");
	}

	// ------------------------------------------------
	// Genera Formulario
	// ------------------------------------------------
	function _Frm($Accion){
		// Conexion
		$Cnx = $this->DB;
		
		// Formulario
		$Form = new nyiHTML('prendas/categoria-prenda-frm.htm');
		$Form->assign('ACC', $Accion);
		$Form->assign('ERROR',$this->Error);

		// Datos
		$Form->assign('id_categoria_prenda', $this->Registro['id_categoria_prenda']);
		$Form->assign('id_linea', $this->Registro['id_linea']);
		$Form->assign('id_categoria_padre', $this->Registro['id_categoria_padre'] == '' ? 0 : $this->Registro['id_categoria_padre']);
		$Form->assign('nombre_categoria_prenda', $this->Registro['nombre_categoria_prenda']);
		$Form->assign('destacada', $this->Registro['destacada'] == 1 ? 'checked="checked"' : '');
		
		$obLineas = new LineaPrenda($Cnx);
		if($Accion == ACC_BAJA || $Accion == ACC_CONSULTA){
			// Si es una baja o consulta, no dejar editar
			$Form->assign('SOLO_LECTURA', 'readonly');
			$Form->assign('linea_txt', $obLineas->nombre($this->Registro['id_linea']));
			$Form->assign('categoria_padre_txt', $this->nombre($this->Registro['id_categoria_padre'] == '' ? 0 : $this->Registro['id_categoria_padre']));
		}
		else{
			// Combo de Lineas
			$Form->assign('linea_id', $obLineas->GetComboIds());
			$Form->assign('linea_nom', $obLineas->GetComboNombres());
			
			// Combo de categorias padre
			$Form->assign('categoria_prenda_id', $this->GetComboIdsParaPadre($this->Registro['id_categoria_prenda'], true, 0));
			$Form->assign('categoria_prenda_nom', $this->GetComboNombresParaPadre($this->Registro['id_categoria_prenda'], true, ''));
		}
		
		// Archivos ya subidos
		if($this->Registro['id_categoria_prenda'] != ""){
			$src = $this->getUrlFoto($this->Registro['id_categoria_prenda']);
			$Form->assign('src_foto', $src);
		}
		
		// Script Post
		$Form->assign('SCRIPT_POST',basename($_SERVER['SCRIPT_NAME']).$Form->fetchParamURL($_GET));
	
		// Cabezal
		$Cab = new nyiHTML('base_cabezal_abm.htm');
		$Cab->assign('NOMFORM', 'ADMINISTRAR CATEGORIAS DE PRENDAS');
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
	
		// Contenido
		return($Form->fetchHTML());
	}

	// ------------------------------------------------
	// Cargo campos desde la base de datos
	// ------------------------------------------------
	function _GetDB($Cod=-1, $Campo='id_categoria_prenda'){
		// Cargo campos
		$this->Registro[$Campo] = $Cod;
		$this->TablaDB->getRegistro($this->Registro, $Campo);
	}
	
	// ------------------------------------------------
	// Cargo campos desde el formulario
	// ------------------------------------------------
	function _GetFrm(){
		// Cargo desde el formulario
		$id = $_POST['id_categoria_prenda'];
		$this->Registro['id_categoria_prenda'] = $id;
		$this->Registro['id_linea'] = $_POST['id_linea'];
		$this->Registro['nombre_categoria_prenda'] = $_POST['nombre_categoria_prenda'];
		$this->Registro['id_categoria_padre'] = $_POST['id_categoria_padre'] == 0 ? NULL : $_POST['id_categoria_padre'];
		$this->Registro['destacada'] = $_POST['destacada'] ? 1 : 0;
		if(is_uploaded_file($_FILES["foto"]['tmp_name']) && $_FILES["foto"]['size'] > 0){
			// Archivo imagen
			$fileName = $_FILES["foto"]['name'];
			$tmpName  = $_FILES["foto"]['tmp_name'];
			$fileSize = $_FILES["foto"]['size'];
			$fileType = $_FILES["foto"]['type'];
			
			$ImgHandler = new ImageHandler();
			// Imagen Preview
			if ($ImgHandler->open_image($tmpName) == 0){
				// Ajusta la imagen si es necesario
				$ImgHandler->resize_image(LARGO_FOTO_CATEGORIA_PRENDA, ANCHO_FOTO_CATEGORIA_PRENDA);
				// La guarda
				$ImgHandler->image_to_file($tmpName);
			}
			
			$this->imgContents = file_get_contents($tmpName);
		}
	}

	// ------------------------------------------------
	// Devuelve html de la Grid
	// ------------------------------------------------
	function grid($Regs){
		// Datos
		$Grid = $this->_Registros($Regs);
		// devuelvo
		return ($Grid->fetchGrid('prendas/categoria-prenda-grid.htm', 'ADMINISTRAR CATEGORÍAS DE PRENDAS',
								basename($_SERVER['SCRIPT_NAME']), // Paginador
								"", // PDF
								basename($_SERVER['SCRIPT_NAME']), // Home
								basename($_SERVER['SCRIPT_NAME']), // Mto
								$this->AccionesGrid));
	}
	
	function getLastId(){
		return $this->DB->getOne("SELECT max(id_categoria_prenda) FROM categoria_prenda");
	}

	// Retorna el combo de identificadores ordenados segun nombre
	function GetComboIds($Todos=false, $IdT=0){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT id_categoria_prenda FROM categoria_prenda ORDER BY nombre_categoria_prenda");
		
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($IdT),$Col);
		}
		return($Col);
	}
	
	// ------------------------------------------------
	// Devuelvo array de detalles para combo
	// ------------------------------------------------
	function GetComboNombres($Todos=false,$NomT='Todos'){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT nombre_categoria_prenda FROM categoria_prenda ORDER BY nombre_categoria_prenda");
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($NomT),$Col);
		}
		return($Col);
	}
	
	// Retorna el combo de identificadores ordenados segun nombre
	function GetComboIdsParaprenda($Todos=false, $IdT=0){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT c.id_categoria_prenda FROM categoria_prenda c WHERE NOT EXISTS(SELECT * FROM categoria_prenda h WHERE h.id_categoria_padre = c.id_categoria_prenda) ORDER BY c.nombre_categoria_prenda");
		
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($IdT),$Col);
		}
		return($Col);
	}
	
	// ------------------------------------------------
	// Devuelvo array de detalles para combo
	// ------------------------------------------------
	function GetComboNombresParaprenda($Todos=false,$NomT='Todos'){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT c.nombre_categoria_prenda FROM categoria_prenda c WHERE NOT EXISTS(SELECT * FROM categoria_prenda h WHERE h.id_categoria_padre = c.id_categoria_prenda) ORDER BY c.nombre_categoria_prenda");
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($NomT),$Col);
		}
		return($Col);
	}
	
	// Retorna el combo de identificadores ordenados por nombre
	// menos excluyendo el de id. igual al parametro y que no tenga
	// ni catalogos ni prendas asociados
	function GetComboIdsParaPadre($id_excluir, $Todos=false, $IdT=0){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT c.id_categoria_prenda FROM categoria_prenda c WHERE c.id_categoria_prenda <> '$id_excluir' AND NOT EXISTS(SELECT * FROM prenda p WHERE p.id_categoria_prenda = c.id_categoria_prenda) ORDER BY nombre_categoria_prenda");
		
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($IdT),$Col);
		}
		return($Col);
	}
	
	// ----------------------------------------------------------
	// Devuelvo array de detalles para combo ordenados por nombre
	// excluyendo el de id. igual al parametro
	// ----------------------------------------------------------
	function GetComboNombresParaPadre($id_excluir, $Todos=false,$NomT='Todos'){
		$Aux = $this->DB;
		$sql = "SELECT c.nombre_categoria_prenda FROM categoria_prenda c WHERE c.id_categoria_prenda <> '$id_excluir' AND NOT EXISTS(SELECT * FROM prenda p WHERE p.id_categoria_prenda = c.id_categoria_prenda) ORDER BY nombre_categoria_prenda";
		$Col = $Aux->getCol($sql);
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($NomT),$Col);
		}
		return($Col);
	}
	
	function ObtenerCategorias(){
		return $this->DB->execute("SELECT * FROM categoria_prenda");
	}
	
	function beforeDelete($id){
		$cntHijos = $this->DB->getOne("SELECT COUNT(*) FROM categoria_prenda WHERE id_categoria_padre = $id");
		if($cntHijos > 0){
			$this->Error .= "La categoria tiene $cntHijos subcategorias asociadas. ";
		}
		$cntProds = $this->DB->getOne("SELECT COUNT(*) FROM prenda WHERE id_categoria_prenda = $id");
		if($cntProds > 0){
			$this->Error .= "La categoria tiene $cntProds prendas asociados. ";
		}
	}
	
	function obtenerDatos($idCategoria, $campos=null){
		$qCampos = "*";
		if(is_array($campos)){
			$qCampos = implode(",", $campos);
		}
		$q = "SELECT $qCampos FROM categoria_prenda WHERE id_categoria_prenda = $idCategoria";
		return iterator_to_array($this->DB->execute($q));
	}
	
	function obtenerDatosCategoriasHijas($idCategoria){
		$Cnx = $this->DB;
		$Datos = array();
		$q = "SELECT * FROM categoria_prenda WHERE id_categoria_padre = $idCategoria ORDER BY nombre_categoria_prenda";
		$qr = $Cnx->execute($q);
		while(!$qr->EOF){
			array_push($Datos, array(
				'id_categoria_prenda'=>$qr->fields['id_categoria_prenda'],
				'nombre_categoria_prenda'=>$qr->fields['nombre_categoria_prenda']
			));
			$qr->MoveNext();
		}
		return $Datos;
	}
	
	function categoriasPrincipalesPorLinea($idLinea, $campos=null){
		$qCampos = "*";
		if(is_array($campos)){
			$qCampos = implode(",", $campos);
		}
		$q = "SELECT $qCampos FROM categoria_prenda WHERE id_categoria_padre IS NULL AND id_linea = $idLinea ORDER BY nombre_categoria_prenda";
		return iterator_to_array($this->DB->execute($q)); 
	}
	
	function categoriasConPrendasPorLinea($idLinea, $campos=null){
		$qCampos = "*";
		if(is_array($campos)){
			$qCampos = implode(",", $campos);
		}
		$q =  "SELECT $qCampos FROM categoria_prenda c ";
		$q .= "WHERE c.id_linea = $idLinea AND EXISTS(";
		$q .= "SELECT p.id_prenda FROM prenda p WHERE p.id_categoria_prenda = c.id_categoria_prenda AND p.visible = 1) ";
		$q .= "ORDER BY c.destacada DESC, c.nombre_categoria_prenda";
		return iterator_to_array($this->DB->execute($q)); 
	}
	
	function obtenerSubcategorias($idCategoria, $campos=null){
		$qCampos = "*";
		if(is_array($campos)){
			$qCampos = implode(",", $campos);
		}
		$q = "SELECT $qCampos FROM categoria_prenda WHERE id_categoria_padre = $idCategoria ORDER BY nombre_categoria_prenda";
		return iterator_to_array($this->DB->execute($q));
	}
	
	protected function afterInsert($id){
		$this->salvarContenidoFoto($id);
	}
	
	protected function afterEdit(){
		$id = (int) $this->Registro['id_categoria_prenda'];
		$this->salvarContenidoFoto($id);
	}
	
	protected function afterDelete($id){
		$dir = DIR_FOTOS_CATEGORIAS_PRENDAS.$id;
		BorrarDirectorio($dir);
	}
	
	private function salvarContenidoFoto($id){
		if($this->imgContents != ""){
			$dir = DIR_FOTOS_CATEGORIAS_PRENDAS . $id;
			if(!file_exists($dir)){
				mkdir($dir);
				chmod($dir, 0755);
			}
			file_put_contents("$dir/standard.jpg", $this->imgContents);
			LogArchivo(md5($this->imgContents));
		}
	}
	
	function getUrlFoto($id){
		return DIR_HTTP_FOTOS_CATEGORIAS_PRENDAS."$id/standard.jpg?t=" . time();
	}
	
	function obtenerFoto($id){
		return $this->DB->getOne("SELECT foto FROM categoria_prenda WHERE id_categoria_prenda = $id");
	}
}
?>