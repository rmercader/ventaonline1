<?PHP

// includes
include_once(DIR_BASE.'class/table.class.php');
include_once(DIR_BASE.'class/departamento.class.php');

class Invitado extends Table {

	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function Invitado($DB, $AJAX=''){
		// Conexion
		$this->Table($DB, 'invitado');
		$this->setSoloLectura();
		// Ajax
		$this->Ajax = $AJAX;
	}
	
	function setSoloLectura(){
		$this->AccionesGrid = array(ACC_CONSULTA);
	}
	
	// ------------------------------------------------
	// Prepara datos para Grid y PDF's
	// ------------------------------------------------
	function _Registros($Regs=0){
		// Creo grid
		$Grid  = new nyiGridDB('COMPRADORES COMO INVITADO', $Regs, 'base_grid.htm');
		
		// Configuro
		$Grid->setParametros(isset($_GET['PVEZ']), 'c.nombre'); // Parametros de la sesion
		$Grid->setPaginador('base_navegador.htm');
		$filtros = array(
			'c.nombre'=>'Nombre', 
			'c.apellido'=>'Apellido', 
			'c.email'=>'Email',
			'd.nombre_departamento'=>'Departamento',
			'c.ciudad'=>'Ciudad',
			'c.direccion'=>'Dirección',
			'c.codigo_postal'=>'Código postal',
			'c.telefono'=>'Teléfono'
		);
		$Grid->setFrmCriterio('base_criterios_buscador.htm', $filtros);
	
		// Si viene con post
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			$Grid->setCriterio($_POST['ORDEN_CAMPO'],$_POST['ORDEN_TXT'],$_POST['CBPAGINA']);
			unset($_GET['NROPAG']);
		}
		
		// Numero de Pagina
		if (isset($_GET['NROPAG'])){
			$Grid->setPaginaAct($_GET['NROPAG']);
		}
			
		$campos = "c.id_invitado AS id, c.nombre, c.apellido, c.email, d.nombre_departamento, c.ciudad, c.direccion, c.codigo_postal, c.telefono";
		$from = "invitado c INNER JOIN departamento d ON d.id_departamento = c.id_departamento";
			
		$Grid->getDatos($this->DB, $campos, $from);
		
		// Devuelvo
		return($Grid);
	}
	
	function getNombre($idInvitado){
		return $this->DB->getOne("SELECT nombre FROM invitado WHERE id_invitado = {$idInvitado}");
	}
	
	function getApellido($idInvitado){
		return $this->DB->getOne("SELECT apellido FROM invitado WHERE id_invitado = {$idInvitado}");
	}
	
	function getEmail($idInvitado){
		return $this->DB->getOne("SELECT email FROM invitado WHERE id_invitado = {$idInvitado}");
	}

	// ------------------------------------------------
	// Genera Formulario
	// ------------------------------------------------
	function _Frm($Accion){
		// Conexion
		$Cnx = $this->DB;
		
		// Objetos
		$depto = new Departamento($Cnx);
		
		// Formulario
		$Form = new nyiHTML('ventas/invitado-frm.htm');
		$Form->assign('ACC', $Accion);
		$Form->assign('ERROR',$this->Error);

		// Datos
		$Form->assign('id_invitado', $this->Registro['id_invitado']);
		$Form->assign('nombre', $this->Registro['nombre']);
		$Form->assign('apellido', $this->Registro['apellido']);
		$Form->assign('email', $this->Registro['email']);
		$Form->assign('direccion', $this->Registro['direccion']);
		$Form->assign('departamento', $depto->getNombre($this->Registro['id_departamento']));
		$Form->assign('ciudad', $this->Registro['ciudad']);
		$Form->assign('codigo_postal', $this->Registro['codigo_postal']);
		$Form->assign('telefono', $this->Registro['telefono']);
		
		if($Accion == ACC_BAJA || $Accion == ACC_CONSULTA){
			// Si es una baja o consulta, no dejar editar
			$Form->assign('SOLO_LECTURA', 'readonly');
		}
		
		// Ventas asociadas
		$ventas = iterator_to_array($this->obtenerVentasAsociadas($this->Registro['id_invitado']));
		$Form->assign('ventas', $ventas);
		
		// Script Post
		$Form->assign('SCRIPT_POST',basename($_SERVER['SCRIPT_NAME']).$Form->fetchParamURL($_GET));
	
		// Cabezal
		$Cab = new nyiHTML('base_cabezal_abm.htm');
		$Cab->assign('NOMFORM', 'COMPRADORES COMO INVITADO');
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
	function _GetDB($Cod=-1, $Campo='id_invitado'){
		// Cargo campos
		$this->Registro[$Campo] = $Cod;
		$this->TablaDB->getRegistro($this->Registro, $Campo);
	}
	
	// ------------------------------------------------
	// Cargo campos desde el formulario
	// ------------------------------------------------
	function _GetFrm(){
		// Cargo desde el formulario
		$id = $_POST['id_invitado'];
		$this->Registro['id_invitado'] = $id;
		$this->Registro['nombre'] = $_POST['nombre'];
		$this->Registro['apellido'] = $_POST['apellido'];
		$this->Registro['email'] = $_POST['email'];
		$this->Registro['direccion'] = $_POST['direccion'];
		$this->Registro['id_departamento'] = $_POST['id_departamento'];
		$this->Registro['ciudad'] = $_POST['ciudad'];
		$this->Registro['codigo_postal'] = $_POST['codigo_postal'];
		$this->Registro['telefono'] = $_POST['telefono'];
	}

	// ------------------------------------------------
	// Devuelve html de la Grid
	// ------------------------------------------------
	function grid($Regs){
		// Datos
		$Grid = $this->_Registros($Regs);
		// devuelvo
		return ($Grid->fetchGrid('ventas/invitado-grid.htm', 'COMPRADORES COMO INVITADO',
								basename($_SERVER['SCRIPT_NAME']), // Paginador
								"", // PDF
								basename($_SERVER['SCRIPT_NAME']), // Home
								basename($_SERVER['SCRIPT_NAME']), // Mto
								$this->AccionesGrid));
	}
	
	function getLastId(){
		return $this->DB->getOne("SELECT max(id_invitado) FROM invitado");
	}

	// Retorna el combo de identificadores ordenados segun nombre
	function getComboIds($Todos=false, $IdT=0){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT id_invitado FROM invitado ORDER BY CONCAT(CONCAT(nombre, ' '), apellido)");
		
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($IdT),$Col);
		}
		return($Col);
	}
	
	// ------------------------------------------------
	// Devuelvo array de deinvitados para combo
	// ------------------------------------------------
	function getComboNombres($Todos=false,$NomT='Todos'){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT CONCAT(CONCAT(nombre, ' '), apellido) FROM invitado ORDER BY CONCAT(CONCAT(nombre, ' '), apellido)");
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($NomT),$Col);
		}
		return($Col);
	}
	
	function obtenerInvitados(){
		return iterator_to_array($this->DB->execute("SELECT * FROM invitado ORDER BY CONCAT(CONCAT(nombre, ' '), apellido)"));
	}
	
	function beforeDelete($id){
		$cntProds = $this->DB->getOne("SELECT COUNT(*) FROM prenda_invitado WHERE id_invitado = $id");
		if($cntProds > 0){
			$this->Error .= "El invitado tiene $cntProds ventas asociadas. ";
		}
	}
	
	function obtenerIdPorEmail($mailInvitado){
		return $this->DB->getOne("SELECT id_invitado FROM invitado WHERE email = '$mailInvitado'");
	}
	
	function insertar(){
		$res = $this->TablaDB->addRegistro($this->Registro);
		if($res == ''){
			$this->Registro["id_invitado"] = $this->getLastId();
		}
		return $res;
	}
	
	function editar(){
		return $this->TablaDB->editRegistro($this->Registro, 'id_invitado');
	}
	
	function obtenerDatosPorVenta($idVenta){
		return $this->DB->execute("SELECT * FROM invitado WHERE id_venta = $idVenta");
	}
	
	function obtenerVentasAsociadas($idInvitado){
		$q = "";
		$q .= "SELECT v.*, DATE_FORMAT(v.fecha, '%e/%c/%Y %H:%i') AS fechaDsc, (v.total-v.costo_envio) AS subtotal ";
		$q .= "FROM venta v INNER JOIN invitado i ON i.id_venta = v.id_venta AND i.id_invitado = $idInvitado ORDER BY v.fecha DESC";
		return $this->DB->execute($q);
	}
}
?>