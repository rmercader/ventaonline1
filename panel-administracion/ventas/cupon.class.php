<?PHP

// includes
include_once(DIR_BASE.'class/table.class.php');
//include_once(DIR_BASE.'class/departamento.class.php');

class Cupon extends Table {

	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function Cupon($DB, $AJAX=''){
		// Conexion
		$this->Table($DB, 'cupon');
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
		$Grid  = new nyiGridDB('CUPONES', $Regs, 'base_grid.htm');
		
		// Configuro
		$Grid->setParametros(isset($_GET['PVEZ']), 'c.nombre'); // Parametros de la sesion
		$Grid->setPaginador('base_navegador.htm');
		$filtros = array(
			'c.nombre'=>'Nombre', 
			'c.Tipo'=>'Tipo', 
			'c.Valor'=>'Valor',
			'd.PrefijoCodigo'=>'Prefijo Codigo',
			'c.FechaIni'=>'Fecha Inicio',
			'c.FechaFin'=>'Fecha Fin'
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
			
		$campos = "c.id_cupon AS id, c.Nombre, c.Tipo, c.Valor, d.PrefijoCodigo, c.FechaIni, c.FechaFin";
		$from = "cupon c ";
			
		$Grid->getDatos($this->DB, $campos, $from);
		
		// Devuelvo
		return($Grid);
	}
	
	function getNombre($idCupon){
		return $this->DB->getOne("SELECT nombre FROM cupon WHERE id_cupon = {$idCupon}");
	}
	
	function getTipo($idCupon){
		return $this->DB->getOne("SELECT Tipo FROM cupon WHERE id_cupon = {$idCupon}");
	}
	
	function getValor($idCupon){
		return $this->DB->getOne("SELECT Valor FROM cupon WHERE id_cupon = {$idCupon}");
	}

	// ------------------------------------------------
	// Genera Formulario
	// ------------------------------------------------
	function _Frm($Accion){
		//TODO: Implementar para el caso de los cupones

		// // Conexion
		// $Cnx = $this->DB;
		
		// // Objetos
		// $depto = new Departamento($Cnx);
		
		// // Formulario
		// $Form = new nyiHTML('ventas/invitado-frm.htm');
		// $Form->assign('ACC', $Accion);
		// $Form->assign('ERROR',$this->Error);

		// // Datos
		// $Form->assign('id_invitado', $this->Registro['id_invitado']);
		// $Form->assign('nombre', $this->Registro['nombre']);
		// $Form->assign('apellido', $this->Registro['apellido']);
		// $Form->assign('email', $this->Registro['email']);
		// $Form->assign('direccion', $this->Registro['direccion']);
		// $Form->assign('departamento', $depto->getNombre($this->Registro['id_departamento']));
		// $Form->assign('ciudad', $this->Registro['ciudad']);
		// $Form->assign('codigo_postal', $this->Registro['codigo_postal']);
		// $Form->assign('telefono', $this->Registro['telefono']);
		
		// if($Accion == ACC_BAJA || $Accion == ACC_CONSULTA){
		// 	// Si es una baja o consulta, no dejar editar
		// 	$Form->assign('SOLO_LECTURA', 'readonly');
		// }
		
		// // Ventas asociadas
		// $ventas = iterator_to_array($this->obtenerVentasAsociadas($this->Registro['id_invitado']));
		// $Form->assign('ventas', $ventas);
		
		// // Script Post
		// $Form->assign('SCRIPT_POST',basename($_SERVER['SCRIPT_NAME']).$Form->fetchParamURL($_GET));
	
		// // Cabezal
		// $Cab = new nyiHTML('base_cabezal_abm.htm');
		// $Cab->assign('NOMFORM', 'COMPRADORES COMO INVITADO');
		// $Cab->assign('NOMACCION', getNomAccion($Accion));
		// $Cab->assign('ACC', $Accion);
		
		// // Script Listado
		// $Parametros = $_GET;
		// unset($Parametros['ACC']);
		// unset($Parametros['COD']);
		// $Cab->assign('SCRIPT_LIS', basename($_SERVER['SCRIPT_NAME']).$Cab->fetchParamURL($Parametros));
		// // Script Salir
		// $Cab->assign('SCRIPT_SALIR', basename($_SERVER['SCRIPT_NAME']).$Cab->fetchParamURL($Parametros));
		// $Form->assign('NAVEGADOR', $Cab->fetchHTML());
	
		// // Contenido
		// return($Form->fetchHTML());
	}
	
	// ------------------------------------------------
	// Cargo campos desde la base de datos
	// ------------------------------------------------
	function _GetDB($Cod=-1, $Campo='id_cupon'){
		// Cargo campos
		$this->Registro[$Campo] = $Cod;
		$this->TablaDB->getRegistro($this->Registro, $Campo);
	}
	
	// ------------------------------------------------
	// Cargo campos desde el formulario
	// ------------------------------------------------
	function _GetFrm(){
		// Cargo desde el formulario
		$id = $_POST['id_cupon'];
		$this->Registro['id_cupon'] = $id;
		$this->Registro['Nombre'] = $_POST['Nombre'];
		$this->Registro['Tipo'] = $_POST['Tipo'];
		$this->Registro['Valor'] = $_POST['Valor'];
		$this->Registro['PrefijoCodigo'] = $_POST['PrefijoCodigo'];
		$this->Registro['FechaIni'] = $_POST['FechaIni'];
		$this->Registro['FechaFin'] = $_POST['FechaFin'];
	}

	// ------------------------------------------------
	// Devuelve html de la Grid
	// ------------------------------------------------
	function grid($Regs){
		//TODO: Implementar para el caso de los cupones
		// // Datos
		// $Grid = $this->_Registros($Regs);
		// // devuelvo
		// return ($Grid->fetchGrid('ventas/invitado-grid.htm', 'COMPRADORES COMO INVITADO',
		// 						basename($_SERVER['SCRIPT_NAME']), // Paginador
		// 						"", // PDF
		// 						basename($_SERVER['SCRIPT_NAME']), // Home
		// 						basename($_SERVER['SCRIPT_NAME']), // Mto
		// 						$this->AccionesGrid));
	}
	
	function getLastId(){
		return $this->DB->getOne("SELECT max(id_cupon) FROM cupon");
	}

	// Retorna el combo de identificadores ordenados segun nombre
	function getComboIds($Todos=false, $IdT=0){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT id_cupon FROM cupon ORDER BY nombre");
		
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($IdT),$Col);
		}
		return($Col);
	}
	
	// ------------------------------------------------
	// Devuelvo array de cupones para combo
	// ------------------------------------------------
	function getComboNombres($Todos=false,$NomT='Todos'){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT nombre FROM cupon ORDER BY nombre");
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($NomT),$Col);
		}
		return($Col);
	}
	
	function obtenerCupones(){
		return iterator_to_array($this->DB->execute("SELECT * FROM cupon ORDER BY nombre"));
	}
	
	function beforeDelete($id){
		$cntProds = $this->DB->getOne("SELECT COUNT(*) FROM cupon_codigo WHERE id_cupon = $id");
		if($cntProds > 0){
			$this->Error .= "El cupon tiene $cntProds Codigos de cupon asociados. ";
		}
	}
	
	
	function insertar(){
		$res = $this->TablaDB->addRegistro($this->Registro);
		if($res == ''){
			$this->Registro["id_cupon"] = $this->getLastId();
		}
		return $res;
	}
	
	function editar(){
		return $this->TablaDB->editRegistro($this->Registro, 'id_cupon');
	}
	
	// function obtenerDatosPorVenta($idVenta){
	// 	return $this->DB->execute("SELECT * FROM invitado WHERE id_venta = $idVenta");
	// }
	
	// function obtenerVentasAsociadas($idInvitado){
	// 	$q = "";
	// 	$q .= "SELECT v.*, DATE_FORMAT(v.fecha, '%e/%c/%Y %H:%i') AS fechaDsc, (v.total-v.costo_envio) AS subtotal ";
	// 	$q .= "FROM venta v INNER JOIN invitado i ON i.id_venta = v.id_venta AND i.id_invitado = $idInvitado ORDER BY v.fecha DESC";
	// 	return $this->DB->execute($q);
	// }
}
?>