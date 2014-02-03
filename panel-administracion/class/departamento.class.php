<?PHP

// includes
include_once(DIR_BASE.'class/table.class.php');

class Departamento extends Table {

	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function Departamento($DB){
		// Conexion
		$this->Table($DB, 'departamento');
		$this->AccionesGrid = array(ACC_MODIFICACION, ACC_CONSULTA);
	}
	
	// ------------------------------------------------
	// Prepara datos para Grid y PDF's
	// ------------------------------------------------
	function _Registros($Regs=0){
		// Creo grid
		$Grid  = new nyiGridDB('ADMINISTRAR DEPARTAMENTOS Y ENVÍOS', $Regs, 'base_grid.htm');
		
		// Configuro
		$Grid->setParametros(isset($_GET['PVEZ']), 'nombre_departamento'); // Parametros de la sesion
		$Grid->setPaginador('base_navegador.htm');
		$Grid->setFrmCriterio('base_criterios_buscador.htm', array('nombre_departamento'=>'Nombre', 'costo_envio'=>'Costo de envío'));
	
		// Si viene con post
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			$Grid->setCriterio($_POST['ORDEN_CAMPO'],$_POST['ORDEN_TXT'],$_POST['CBPAGINA']);
			unset($_GET['NROPAG']);
		}
		
		// Numero de Pagina
		if (isset($_GET['NROPAG'])){
			$Grid->setPaginaAct($_GET['NROPAG']);
		}
			
		$campos = "id_departamento AS id, nombre_departamento, costo_envio";
		$from = "departamento";
			
		$Grid->getDatos($this->DB, $campos, $from);
		
		// Devuelvo
		return($Grid);
	}
	
	// ------------------------------------------------
	// Genera Formulario
	// ------------------------------------------------
	function _Frm($Accion){
		// Conexion
		$Cnx = $this->DB;
		
		// Formulario
		$Form = new nyiHTML('parametros/departamento-frm.htm');
		$Form->assign('ACC', $Accion);
		$Form->assign('ERROR',$this->Error);

		// Datos
		$Form->assign('id_departamento', $this->Registro['id_departamento']);
		$Form->assign('nombre_departamento', $this->Registro['nombre_departamento']);
		$Form->assign('costo_envio', $this->Registro['costo_envio']);
		
		if($Accion == ACC_BAJA || $Accion == ACC_CONSULTA){
			// Si es una baja o consulta, no dejar editar
			$Form->assign('SOLO_LECTURA', 'readonly');
		}
		
		// Script Post
		$Form->assign('SCRIPT_POST',basename($_SERVER['SCRIPT_NAME']).$Form->fetchParamURL($_GET));
	
		// Cabezal
		$Cab = new nyiHTML('base_cabezal_abm.htm');
		$Cab->assign('NOMFORM', 'ADMINISTRAR DEPARTAMENTOS Y ENVÍOS');
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
	function _GetDB($Cod=-1, $Campo='id_departamento'){
		// Cargo campos
		$this->Registro[$Campo] = $Cod;
		$this->TablaDB->getRegistro($this->Registro, $Campo);
	}
	
	// ------------------------------------------------
	// Cargo campos desde el formulario
	// ------------------------------------------------
	function _GetFrm(){
		// Cargo desde el formulario
		$id = $_POST['id_departamento'];
		$this->Registro['id_departamento'] = $id;
		$this->Registro['nombre_departamento'] = $_POST['nombre_departamento'];
		$this->Registro['costo_envio'] = $_POST['costo_envio'];
		if(!is_numeric($_POST['costo_envio'])){
			$this->Error .= "El costo de envío debe ser un número válido.";
		}
	}

	// ------------------------------------------------
	// Devuelve html de la Grid
	// ------------------------------------------------
	function grid($Regs){
		// Datos
		$Grid = $this->_Registros($Regs);
		// devuelvo
		return ($Grid->fetchGrid('parametros/departamento-grid.htm', 'ADMINISTRAR DEPARTAMENTOS Y ENVÍOS',
								basename($_SERVER['SCRIPT_NAME']), // Paginador
								"", // PDF
								basename($_SERVER['SCRIPT_NAME']), // Home
								basename($_SERVER['SCRIPT_NAME']), // Mto
								$this->AccionesGrid));
	}
	
	// Retorna el combo de identificadores ordenados segun nombre
	function GetComboIds($Todos=false, $IdT=0){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT id_departamento FROM departamento ORDER BY id_departamento");
		
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
		$Col = $Aux->getCol("SELECT nombre_departamento FROM departamento ORDER BY id_departamento");
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($NomT),$Col);
		}
		return($Col);
	}
	
	function getNombre($idDepartamento){
		return $this->DB->getOne("SELECT nombre_departamento FROM departamento WHERE id_departamento = {$idDepartamento}");
	}
	
	function getCostoEnvio($idDepartamento){
		return $this->DB->getOne("SELECT costo_envio FROM departamento WHERE id_departamento = {$idDepartamento}");
	}
}
?>