<?PHP

// includes
include_once(DIR_BASE.'class/table.class.php');
include_once(DIR_BASE.'class/image-handler.class.php');
include_once(DIR_BASE.'prendas/prenda.class.php');

class Coleccion extends Table {

	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function Coleccion($DB, $AJAX=''){
		// Conexion
		$this->Table($DB, 'coleccion');
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
		$Grid  = new nyiGridDB('ADMINISTRAR COLECCIONES DE PRENDAS', $Regs, 'base_grid.htm');
		
		// Configuro
		$Grid->setParametros(isset($_GET['PVEZ']), 'c.nombre_coleccion'); // Parametros de la sesion
		$Grid->setPaginador('base_navegador.htm');
		$arrCriterios = array(
			'c.nombre_coleccion'=>'Nombre',
			'l.nombre_linea'=>'Linea',
			'c.id_coleccion'=>'Identificador',
			'c.fecha_desde'=>'Fecha desde', 
			'c.fecha_hasta'=>'Fecha hasta',
		);
		$Grid->setFrmCriterio('base_criterios_buscador.htm', $arrCriterios);
	
		// Si viene con post
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			$Grid->setCriterio($_POST['ORDEN_CAMPO'],$_POST['ORDEN_TXT'],$_POST['CBPAGINA']);
			unset($_GET['NROPAG']);
		}
		
		// Numero de Pagina
		if (isset($_GET['NROPAG'])){
			$Grid->setPaginaAct($_GET['NROPAG']);
		}
			
		$campos = "c.id_coleccion AS id, c.nombre_coleccion, l.nombre_linea, DATE_FORMAT(c.fecha_desde, '%d/%m/%Y') AS fecha_desde, DATE_FORMAT(c.fecha_hasta, '%d/%m/%Y') AS fecha_hasta";
		$from = "coleccion c INNER JOIN linea l ON l.id_linea = c.id_linea";
			
		$Grid->getDatos($this->DB, $campos, $from);
		
		// Devuelvo
		return($Grid);
	}

	function ObtenerColeccionesConPrendas($campos=null){
        $qCampos = "*";
		if(is_array($campos)){
			$qCampos = implode(",", $campos);
		}
		$q = "SELECT $qCampos FROM coleccion c WHERE NOW() BETWEEN c.fecha_desde AND c.fecha_hasta AND EXISTS (SELECT cp.id_prenda FROM coleccion_prenda cp WHERE cp.id_coleccion = c.id_coleccion)";
		return iterator_to_array($this->DB->execute($q));
	}
	
	function getNombre($idColeccion){
		return $this->DB->getOne("SELECT nombre_coleccion FROM coleccion WHERE id_coleccion = {$idColeccion}");
	}

	// ------------------------------------------------
	// Genera Formulario
	// ------------------------------------------------
	function _Frm($Accion){
		// Conexion
		$Cnx = $this->DB;
		$obLineas = new LineaPrenda($Cnx);
		
		// Formulario
		$Form = new nyiHTML('prendas/coleccion-frm.htm');
		$Form->assign('ACC', $Accion);
		$Form->assign('ERROR',$this->Error);

		// Datos
		$Form->assign('id_coleccion', $this->Registro['id_coleccion']);
		$Form->assign('id_linea', $this->Registro['id_linea']);
  		$Form->assign('fecha_desde', $this->Registro['fecha_desde']);
		$Form->assign('fecha_hasta', $this->Registro['fecha_hasta']);
		$Form->assign('nombre_coleccion', $this->Registro['nombre_coleccion']);
		
  		$editor = new FCKeditor('descripcion') ;
		$editor->BasePath = 'fckeditor/' ;
		$editor->Height = ALTURA_EDITOR;
		$editor->Config['EnterMode'] = 'br';
		$editor->Value = $this->Registro['descripcion'];
		$contenido = $editor->CreateHtml();
		$Form->assign('descripcion', $contenido);
		
		if($Accion != ACC_ALTA && $Accion != ACC_MODIFICACION){
			// Si es una baja o consulta, no dejar editar
			$Form->assign('SOLO_LECTURA', 'readonly');
			$Form->assign('linea_txt', $obLineas->nombre($this->Registro['id_linea']));
		}
		else{
			// Combo de Lineas
			$Form->assign('linea_id', $obLineas->GetComboIds());
			$Form->assign('linea_nom', $obLineas->GetComboNombres());
		}
		
		// Archivos ya subidos
		if($this->Registro['id_coleccion'] != ""){
			$src = $this->getUrlFoto($this->Registro['id_coleccion']);
			$Form->assign('src_foto', $src);
		}
		
		if(isset($_GET["REDIRIGIR"])){
			unset($_GET["REDIRIGIR"]);
			$this->informarSalvarOk();
		}
		
		// Script Post
		$Form->assign('SCRIPT_POST',basename($_SERVER['SCRIPT_NAME']).$Form->fetchParamURL($_GET));
	
		// Cabezal
		$Cab = new nyiHTML('base_cabezal_abm.htm');
		$Cab->assign('NOMFORM', 'ADMINISTRAR COLECCIONES DE PRENDAS');
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
	
	function getUrlFoto($id){
		return DIR_HTTP."prendas/foto-coleccion.php?id=".$id;
	}
	
	function obtenerFoto($id){
		return $this->DB->getOne("SELECT foto FROM coleccion WHERE id_coleccion = {$id}");
	}

	// ------------------------------------------------
	// Cargo campos desde la base de datos
	// ------------------------------------------------
	function _GetDB($Cod=-1, $Campo='id_coleccion'){
		// Cargo campos
		$this->Registro[$Campo] = $Cod;
		$this->TablaDB->getRegistro($this->Registro, $Campo);
	}
	
 	function validarFormulario(){
		$fechaIniTime = strtotime($this->Registro['fecha_desde']);
		$fechaFinTime = strtotime($this->Registro['fecha_hasta']);
		if($fechaFinTime < $fechaIniTime){
			$this->Error .= "La fecha hasta debe ser mayor o igual a la fecha desde.\n";
		}

		if(trim($this->Registro['nombre_coleccion']) == ""){
			$this->Error .= "Debe ingresar el nombre de la colección.\n";
		}
	}
	
	// ------------------------------------------------
	// Cargo campos desde el formulario
	// ------------------------------------------------
	function _GetFrm(){
		// Cargo desde el formulario
		$id = $_POST['id_coleccion'];
		$this->Registro['id_coleccion'] = $id;
		$this->Registro['id_linea'] = $_POST['id_linea'];
		$this->Registro['nombre_coleccion'] = $_POST['nombre_coleccion'];
		$this->Registro['descripcion'] = $_POST['descripcion'];
		$this->Registro['fecha_desde'] = $_POST["fecha_desdeYear"]."-".$_POST["fecha_desdeMonth"]."-".$_POST["fecha_desdeDay"];
		$this->Registro['fecha_hasta'] = $_POST["fecha_hastaYear"]."-".$_POST["fecha_hastaMonth"]."-".$_POST["fecha_hastaDay"];

		if(is_uploaded_file($_FILES["foto"]['tmp_name']) && $_FILES["foto"]['size'] > 0){
   			$imgData = file_get_contents($_FILES['foto']['tmp_name']);
			$this->Registro['foto'] = $imgData;
		}
		$this->validarFormulario();
	}

	// ------------------------------------------------
	// Devuelve html de la Grid
	// ------------------------------------------------
	function grid($Regs){
		// Datos
		$Grid = $this->_Registros($Regs);
		// devuelvo
		return ($Grid->fetchGrid('prendas/colecciones-grid.htm', 'ADMINISTRAR COLECCIONES DE PRENDAS',
								basename($_SERVER['SCRIPT_NAME']), // Paginador
								"", // PDF
								basename($_SERVER['SCRIPT_NAME']), // Home
								basename($_SERVER['SCRIPT_NAME']), // Mto
								$this->AccionesGrid));
	}
	
	function getLastId(){
		return $this->DB->getOne("SELECT max(id_coleccion) FROM coleccion");
	}

	// Retorna el combo de identificadores ordenados segun nombre
	function getComboIds($Todos=false, $IdT=0){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT id_coleccion FROM coleccion ORDER BY nombre_coleccion");
		
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
		$Col = $Aux->getCol("SELECT nombre_coleccion FROM coleccion ORDER BY nombre_coleccion");
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($NomT),$Col);
		}
		return($Col);
	}
	
	function obtenerColeccionesVigentes($campos=null){
		$qCampos = "*";
		if(is_array($campos)){
			$qCampos = implode(",", $campos);
		}
		$Cnx = $this->DB;		
		$q = "SELECT $qCampos FROM coleccion WHERE id_coleccion = $idColeccion";
		$qr = $Cnx->execute($q);
		return iterator_to_array($qr);
	}
	
	function obtenerDatos($idColeccion, $campos=null){
		$qCampos = "*";
		if(is_array($campos)){
			$qCampos = implode(",", $campos);
		}
		$Cnx = $this->DB;
		$q = "SELECT $qCampos FROM coleccion WHERE id_coleccion = $idColeccion";
		$qr = $Cnx->execute($q);
		return iterator_to_array($qr);
	}
	
	function obtenerHtmlPrendasAsociadas($idColeccion){
        // Obtengo todos los datos de las prendas asociadas a la coleccion
		$listaPrendas = $this->obtenerPrendas($idColeccion);
		$html = new nyiHTML('prendas/lista-prendas-coleccion.htm');
        $html->assign('REG', count($listaPrendas) > 0 ? $listaPrendas : 0);
		return $html->fetchHTML();
	}
	
	function obtenerPrendas($idColeccion){
		$campos = "p.id_prenda, c.nombre_categoria_prenda, p.nombre_prenda, l.nombre_linea";
		$from = "coleccion_prenda cp INNER JOIN prenda p ON p.id_prenda = cp.id_prenda AND cp.id_coleccion = $idColeccion INNER JOIN categoria_prenda c ON c.id_categoria_prenda = p.id_categoria_prenda INNER JOIN linea l ON l.id_linea = c.id_linea";
		$q = "SELECT $campos FROM $from ORDER BY l.id_linea, c.nombre_categoria_prenda, p.nombre_prenda";
		return iterator_to_array($this->DB->execute($q));
	}
	
	function getIdLinea($idColeccion){
		return $this->DB->getOne("SELECT id_linea FROM coleccion WHERE id_coleccion = $idColeccion");
	}
	
	function obtenerPrendasPorNombreParaAgregar($nomBuscado, $idColeccion){
		$idLinea = $this->getIdLinea($idColeccion);
		$q =  "SELECT p.id_prenda AS id, p.nombre_prenda AS nombre ";
		$q .= "FROM prenda p INNER JOIN categoria_prenda c ON c.id_categoria_prenda = p.id_categoria_prenda INNER JOIN linea l ON l.id_linea = c.id_linea AND l.id_linea = $idLinea ";
		$q .= "WHERE p.nombre_prenda LIKE '%$nomBuscado%' AND ";
		$q .= "NOT EXISTS (SELECT * FROM coleccion_prenda cp WHERE cp.id_coleccion = $idColeccion AND cp.id_prenda = p.id_prenda)";
		return iterator_to_array($this->DB->execute($q));
	}
	
	function agregarPrenda($idPrenda, $idColeccion){
		$cnt = $this->DB->getOne("SELECT COUNT(*) FROM coleccion_prenda id_coleccion = $idColeccion AND id_prenda = $idPrenda");
		if($cnt == 0){
			$q = "INSERT INTO coleccion_prenda(id_coleccion, id_prenda) VALUES($idColeccion, $idPrenda)";
			$ok = $this->DB->execute($q);
			if($ok === false){
				LogError("Error agregando prenda $idPrenda a coleccion $idColeccion\n". $this->DB->ErrorMsg(), basename(__FILE__), "agregarPrenda($idPrenda, $idColeccion)");
				return "Ocurrió un error al agregar la prenda a la colección\n";
			}
		}
		return "";
	}
	
	function agregarPrendasCategoria($idCategoriaPrenda, $idColeccion){
		$errores = "";
		$this->StartTransaction();
		// Obtenemos las prendas de la categoria que no esten asociadas a la coleccion
		$q =  "SELECT p.id_prenda ";
		$q .= "FROM prenda p ";
		$q .= "WHERE p.id_categoria_prenda = $idCategoriaPrenda AND ";
		$q .= "NOT EXISTS (SELECT * FROM coleccion_prenda cp WHERE cp.id_coleccion = $idColeccion AND cp.id_prenda = p.id_prenda)";
		$prendas = iterator_to_array($this->DB->execute($q));
		
		// Las vamos agregando
		foreach($prendas as $prenda){
			$q = "INSERT INTO coleccion_prenda(id_coleccion, id_prenda) VALUES($idColeccion, {$prenda['id_prenda']})";
			$this->DB->execute($q);
		}
		
		$this->CompleteTransaction();
		if($this->DB->ErrorMsg() != ""){
			$errores .= "Ocurrio un error al agregar las prendas a la colección.\n";
			LogError($this->DB->ErrorMsg(), basename(__FILE__), "agregarPrendasCategoria($idCategoriaPrenda, $idColeccion)");
		}
		return $errores;
	}
	
	function eliminarAsociacionPrendas($idColeccion, $idsPrendas){
		$errores = "";
		$q = "DELETE FROM coleccion_prenda WHERE id_coleccion = $idColeccion AND id_prenda IN ($idsPrendas)";
		$ok = $this->DB->execute($q);
		if($ok === false){
			$errores .= "Ocurrio un error al eliminar las prendas de la colección.\n";
			LogError($this->DB->ErrorMsg(), basename(__FILE__), "eliminarAsociacionPrendas($idColeccion, $idsPrendas)");
		}
		return $errores;
	}
	
	function coleccionesConPrendasPorLinea($idLinea){
		$q =  "SELECT c.id_coleccion, c.nombre_coleccion FROM coleccion c ";
		$q .= "WHERE EXISTS (";
		$q .= "SELECT cp.id_coleccion FROM coleccion_prenda cp INNER JOIN prenda p ON cp.id_prenda = p.id_prenda ";
		$q .= "AND p.visible = 1 ";
		$q .= "INNER JOIN categoria_prenda cat ON cat.id_categoria_prenda = p.id_categoria_prenda AND cat.id_linea = $idLinea ";
		$q .= "WHERE cp.id_coleccion = c.id_coleccion) ";
		$q .= "AND NOW() BETWEEN c.fecha_desde AND c.fecha_hasta ";
		$q .= "AND c.id_linea = $idLinea ";
		$q .= "ORDER BY c.fecha_desde DESC, c.nombre_coleccion";
		
		return iterator_to_array($this->DB->execute($q)); 
	}
	
	function afterInsert($id){
		$this->Registro['id_coleccion'] = $id;
	}
	
	function afterEdit(){
		$this->informarSalvarOk();
	}
	
	function informarSalvarOk(){
		$this->Error = "Los datos de la colección se han guardado correctamente.";
	}
}
?>