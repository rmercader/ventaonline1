<?PHP

// includes
include_once(DIR_BASE.'class/table.class.php');
include_once(DIR_BASE.'class/image-handler.class.php');

class Color extends Table {

	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function Color($DB, $AJAX=''){
		// Conexion
		$this->Table($DB, 'color');
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
		$Grid  = new nyiGridDB('ADMINISTRAR COLORES DE PRENDAS', $Regs, 'base_grid.htm');
		
		// Configuro
		$Grid->setParametros(isset($_GET['PVEZ']), 'c.nombre_color'); // Parametros de la sesion
		$Grid->setPaginador('base_navegador.htm');
		$Grid->setFrmCriterio('base_criterios_buscador.htm', array('c.nombre_color'=>'Nombre', 'c.id_color'=>'Identificador'));
	
		// Si viene con post
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			$Grid->setCriterio($_POST['ORDEN_CAMPO'],$_POST['ORDEN_TXT'],$_POST['CBPAGINA']);
			unset($_GET['NROPAG']);
		}
		
		// Numero de Pagina
		if (isset($_GET['NROPAG'])){
			$Grid->setPaginaAct($_GET['NROPAG']);
		}
			
		$campos = "c.id_color AS id, c.nombre_color";
		$from = "color c";
			
		$Grid->getDatos($this->DB, $campos, $from);
		
		// Devuelvo
		return($Grid);
	}
	
	function getNombre($idColor){
		return $this->DB->getOne("SELECT nombre_color FROM color WHERE id_color = {$idColor}");
	}

	// ------------------------------------------------
	// Genera Formulario
	// ------------------------------------------------
	function _Frm($Accion){
		// Conexion
		$Cnx = $this->DB;
		
		// Formulario
		$Form = new nyiHTML('prendas/color-frm.htm');
		$Form->assign('ACC', $Accion);
		$Form->assign('ERROR',$this->Error);

		// Datos
		$Form->assign('id_color', $this->Registro['id_color']);
		$Form->assign('nombre_color', $this->Registro['nombre_color']);
		
		if($Accion == ACC_BAJA || $Accion == ACC_CONSULTA){
			// Si es una baja o consulta, no dejar editar
			$Form->assign('SOLO_LECTURA', 'readonly');
		}
		
		// Archivos ya subidos
		if($this->Registro['id_color'] != ""){
			$src = $this->getUrlImagen($this->Registro['id_color']);
			$Form->assign('src_imagen', $src);
		}
		
		// Script Post
		$Form->assign('SCRIPT_POST',basename($_SERVER['SCRIPT_NAME']).$Form->fetchParamURL($_GET));
	
		// Cabezal
		$Cab = new nyiHTML('base_cabezal_abm.htm');
		$Cab->assign('NOMFORM', 'ADMINISTRAR COLORES DE PRENDAS');
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
	
	function getUrlImagen($id, $thumb=0){
		return DIR_HTTP."prendas/foto-color.php?id=$id&thumb=$thumb";
	}
	
	function obtenerImagen($id, $cmpImg="imagen"){
		//LogArchivo("SELECT $cmpImg FROM color WHERE id_color = $id");
		return $this->DB->getOne("SELECT $cmpImg FROM color WHERE id_color = $id");
	}

	// ------------------------------------------------
	// Cargo campos desde la base de datos
	// ------------------------------------------------
	function _GetDB($Cod=-1, $Campo='id_color'){
		// Cargo campos
		$this->Registro[$Campo] = $Cod;
		$this->TablaDB->getRegistro($this->Registro, $Campo);
	}
	
	// ------------------------------------------------
	// Cargo campos desde el formulario
	// ------------------------------------------------
	function _GetFrm(){
		// Cargo desde el formulario
		$id = $_POST['id_color'];
		$this->Registro['id_color'] = $id;
		$this->Registro['nombre_color'] = $_POST['nombre_color'];
	}

	// ------------------------------------------------
	// Devuelve html de la Grid
	// ------------------------------------------------
	function grid($Regs){
		// Datos
		$Grid = $this->_Registros($Regs);
		$Grid->addVariable('SRC_VISTA', DIR_HTTP . "prendas/foto-color.php?thumb=1");
		// devuelvo
		return ($Grid->fetchGrid('prendas/color-grid.htm', 'ADMINISTRAR COLORES',
								basename($_SERVER['SCRIPT_NAME']), // Paginador
								"", // PDF
								basename($_SERVER['SCRIPT_NAME']), // Home
								basename($_SERVER['SCRIPT_NAME']), // Mto
								$this->AccionesGrid));
	}
	
	function getLastId(){
		return $this->DB->getOne("SELECT max(id_color) FROM color");
	}
	
	function afterInsert($id){
		$this->Registro['id_color'] = $id;
		$this->salvarImagenes();
	}
	
	function afterEdit(){
		$this->salvarImagenes();
	}
	
	function salvarImagenes(){
		$id = $this->Registro['id_color'];
		if(is_uploaded_file($_FILES["imagen"]['tmp_name']) && $_FILES["imagen"]['size'] > 0){
			// Archivo imagen
			$fileName = $_FILES["imagen"]['name'];
			$tmpName  = $_FILES["imagen"]['tmp_name'];
			$fileSize = $_FILES["imagen"]['size'];
			$fileType = $_FILES["imagen"]['type'];
			
			$dir = dirname($tmpName);
			$imgThuSrc = $dir . DIRECTORY_SEPARATOR . "thu-color-$id." . GetExtension($fileName);
			$imgSrc = $dir . DIRECTORY_SEPARATOR . "color-$id." . GetExtension($fileName);
			
			if(file_exists($imgThuSrc)){
				@unlink($imgThuSrc);
			}
			if(file_exists($imgSrc)){
				@unlink($imgSrc);
			}
			
			$ImgHandler = new ImageHandler();
			// Imagen Preview
			if ($ImgHandler->open_image($tmpName) == 0){
				// Ajusta la imagen si es necesario
				$ImgHandler->resize_image(LARGO_IMG_COLOR, ANCHO_IMG_COLOR);
				// La guarda
				$ImgHandler->image_to_file($imgSrc);
				$fp      = fopen($imgSrc, 'r');
				$content = fread($fp, filesize($imgSrc));
				$content = addslashes($content);
				fclose($fp);
				$this->DB->execute("UPDATE color SET imagen = '$content' WHERE id_color = $id");
			}
			
			$ImgHandler = new ImageHandler();
			// Imagen Preview
			if ($ImgHandler->open_image($tmpName) == 0){
				// Ajusta la imagen si es necesario
				$ImgHandler->resize_image(LARGO_THU_COLOR, ANCHO_THU_COLOR);
				// La guarda
				$ImgHandler->image_to_file($imgThuSrc);
				$fp      = fopen($imgThuSrc, 'r');
				$content = fread($fp, filesize($imgThuSrc));
				$content = addslashes($content);
				fclose($fp);
				$this->DB->execute("UPDATE color SET thumbnail = '$content' WHERE id_color = $id");
			}
		}
	}

	// Retorna el combo de identificadores ordenados segun nombre
	function getComboIds($Todos=false, $IdT=0){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT id_color FROM color ORDER BY nombre_color");
		
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
	function getComboNombres($Todos=false,$NomT='Todos'){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT nombre_color FROM color ORDER BY nombre_color");
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($NomT),$Col);
		}
		return($Col);
	}
	
	function obtenerColores(){
		return iterator_to_array($this->DB->execute("SELECT * FROM color"));
	}
	
	function beforeDelete($id){
		$cntProds = $this->DB->getOne("SELECT COUNT(*) FROM prenda_color WHERE id_color = $id");
		if($cntProds > 0){
			$this->Error .= "El color tiene $cntProds prendas asociadas. ";
		}
	}
}
?>