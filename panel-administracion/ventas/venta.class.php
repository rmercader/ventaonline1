<?PHP

// includes
include_once(DIR_BASE.'class/table.class.php');
include_once(DIR_BASE.'ventas/invitado.class.php');
include_once(DIR_BASE.'ventas/comprador.class.php');

class Venta extends Table {

	var $comprador__r;
	var $item__r;
	var $cobro_abitab__r;

	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function Venta($DB, $AJAX=''){
		// Conexion
		$this->Table($DB, 'venta');
		$this->comprador__r = new Table($DB, 'venta_comprador');
		$this->item__r = new Table($DB, 'venta_item');
		$this->cobro_abitab__r = new Table($DB, 'venta_cobro_abitab');
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
		$Grid  = new nyiGridDB('VENTAS', $Regs, 'base_grid.htm');
		
		// Configuro
		//$Grid->setParametros(isset($_GET['PVEZ']), 'v.fecha', 'DESC'); // Parametros de la sesion
		$Grid->setPaginador('base_navegador.htm');
		$filtros = array(
			'v.fecha'=>'Fecha', 
			'v.id_venta'=>'Nro. Orden', 
			'v.estado_pago'=>'Estado del pago',
			'v.medio_pago'=>'Medio de pago',
			'v.costo_envio'=>'Costo de envío',
			'subtotal'=>'SubTotal',
			'v.total'=>'Total',
			'v.descuento'=>'Descuento',
			'v.codigo_Cupon'=>'Código de Cupón'
		);
		
		$Grid->setFrmCriterio('ventas/criterios-buscador.htm', $filtros);
	
		$arrWhere = array();
		// Si viene con post
		if ($_SERVER["REQUEST_METHOD"] == "POST"){
			if(isset($_POST['ORDEN_CAMPO']) && isset($_POST['ORDEN'])){
				unset($_SESSION['buscador-ventas']); // Reinicio los filtros
				$_SESSION['buscador-ventas']['ORDEN_CAMPO'] = $_POST['ORDEN_CAMPO'];
				$_SESSION['buscador-ventas']['ORDEN'] = $_POST['ORDEN'];
			}
			$Grid->setCriterio($_SESSION['buscador-ventas']['ORDEN_CAMPO'], "", 1, $_SESSION['buscador-ventas']['ORDEN']);
			unset($_GET['NROPAG']);
			
			// Criterios
			// Id de venta
			$critIdVenta = addslashes(trim($_POST['id_venta']));
			if($critIdVenta != ''){
				array_push($arrWhere, "(v.id_venta LIKE '%$critIdVenta%')");
				$_SESSION['buscador-ventas']['id_venta'] = $critIdVenta;
			}
			// Fechas
			$critFechaDesde = "{$_POST['fecha_desdeYear']}-{$_POST['fecha_desdeMonth']}-{$_POST['fecha_desdeDay']} 00:00:00";
			$critFechaHasta = "{$_POST['fecha_hastaYear']}-{$_POST['fecha_hastaMonth']}-{$_POST['fecha_hastaDay']} 23:59:59";
			array_push($arrWhere, "(v.fecha BETWEEN '$critFechaDesde' AND '$critFechaHasta')");
			$_SESSION['buscador-ventas']['fecha_desde'] = $critFechaDesde;
			$_SESSION['buscador-ventas']['fecha_hasta'] = $critFechaHasta;
			
			// Estado del pago
			$critEstadoPago = addslashes(trim($_POST['estado_pago']));
			if($critEstadoPago != ''){
				array_push($arrWhere, "(v.estado_pago = '$critEstadoPago')");
				$_SESSION['buscador-ventas']['estado_pago'] = $critEstadoPago;
			}
			
			// Estado de la venta
			$critEstado = addslashes(trim($_POST['estado']));
			if($critEstado != ''){
				array_push($arrWhere, "(v.estado = '$critEstado')");
				$_SESSION['buscador-ventas']['estado'] = $critEstado;			
			}
			
			// Medio de pago
			$critMedioPago = addslashes(trim($_POST['medio_pago']));
			if($critMedioPago != ''){
				array_push($arrWhere, "(v.medio_pago = '$critMedioPago')");
				$_SESSION['buscador-ventas']['medio_pago'] = $critMedioPago;
			}
		}
		else {
			// Esto se hace para comenzar en limpio una nueva busqueda
			if(isset($_GET['PVEZ'])){
				unset($_SESSION['buscador-ventas']);
				$Grid->setCriterio("v.fecha", "", 1, "DESC");
				$_SESSION['buscador-ventas']['ORDEN_CAMPO'] = "v.fecha";
				$_SESSION['buscador-ventas']['ORDEN'] = "DESC";
			}
			else{
				// Criterios
				// Id de venta
				if(isset($_SESSION['buscador-ventas']['id_venta'])){
					array_push($arrWhere, "(v.id_venta LIKE '%{$_SESSION['buscador-ventas']['id_venta']}%')");
				}
				
				// Fechas
				if(isset($_SESSION['buscador-ventas']['fecha_desde']) && isset($_SESSION['buscador-ventas']['fecha_hasta'])){
					$critFechaDesde = $_SESSION['buscador-ventas']['fecha_desde'];
					$critFechaHasta = $_SESSION['buscador-ventas']['fecha_hasta'];
					array_push($arrWhere, "(v.fecha BETWEEN '$critFechaDesde' AND '$critFechaHasta')");
				}
				
				// Estado del pago
				if(isset($_SESSION['buscador-ventas']['estado_pago'])){
					$critEstadoPago = $_SESSION['buscador-ventas']['estado_pago'];
					array_push($arrWhere, "(v.estado_pago = '$critEstadoPago')");
				}
				
				// Estado de la venta
				if(isset($_SESSION['buscador-ventas']['estado'])){
					$critEstado = $_SESSION['buscador-ventas']['estado'];
					array_push($arrWhere, "(v.estado = '$critEstado')");
				}
				
				// Medio de pago
				if(isset($_SESSION['buscador-ventas']['medio_pago'])){
					$critMedioPago = $_SESSION['buscador-ventas']['medio_pago'];
					array_push($arrWhere, "(v.medio_pago = '$critMedioPago')");
				}
				
				if(isset($_SESSION['buscador-ventas']['ORDEN_CAMPO']) && isset($_SESSION['buscador-ventas']['ORDEN'])){
					$Grid->setCriterio($_SESSION['buscador-ventas']['ORDEN_CAMPO'], "", $_GET['NROPAG'], $_SESSION['buscador-ventas']['ORDEN']);
				}
			}
		}
		
		$where = implode(' AND ', $arrWhere);
		
		// Numero de Pagina
		if (isset($_GET['NROPAG'])){
			$Grid->setPaginaAct($_GET['NROPAG']);
		}
			
		$campos = "v.id_venta AS id, v.*, DATE_FORMAT(v.fecha, '%e/%c/%Y %H:%i') AS fechaDsc, (v.total-v.costo_envio+v.descuento) AS subtotal";
		$from = "venta v";
			
		if($where != ''){
			$Grid->getDatos($this->DB, $campos, $from, $where);
		}
		else {
			$Grid->getDatos($this->DB, $campos, $from);
		}
		
		// Devuelvo
		return($Grid);
	}
	
	// ------------------------------------------------
	// Devuelve html de la Grid
	// ------------------------------------------------
	function grid($Regs){
		// Datos
		$Grid = $this->_Registros($Regs);
		
		// Id de venta
		$Grid->assign('id_venta', isset($_POST['id_venta']) ? $_POST['id_venta'] : $_SESSION['buscador-ventas']['id_venta']);
		
		// Combo estados de pago
		$arrEstadosPago = array('pendiente', 'confirmado');
		$Grid->assign('ids_estados_pago', $arrEstadosPago);
		$Grid->assign('dsc_estados_pago', array_map('strtoupper', $arrEstadosPago));
		$Grid->assign('estado_pago', isset($_POST['estado_pago']) ? $_POST['estado_pago'] : $_SESSION['buscador-ventas']['estado_pago']);
		
		// Combo estados
		$arrEstados = array('iniciada', 'enviada', 'completa');
		$Grid->assign('ids_estados', $arrEstados);
		$Grid->assign('dsc_estados', array_map('strtoupper', $arrEstados));
		$Grid->assign('estado', isset($_POST['estado']) ? $_POST['estado'] : $_SESSION['buscador-ventas']['estado']);
		
		// Combo medios de pago
		$Grid->assign('ids_medios', array(0, 1));
		$Grid->assign('dsc_medios', array('ABITAB', 'OCA CARD'));
		$Grid->assign('medio_pago', isset($_POST['medio_pago']) ? $_POST['medio_pago'] : $_SESSION['buscador-ventas']['medio_pago']);
		
		// Combo campos ordenacion
		$filtros = array(
			'v.fecha'=>'Fecha', 
			'v.id_venta'=>'Nro. Orden', 
			'v.estado_pago'=>'Estado del pago',
			'v.medio_pago'=>'Medio de pago',
			'v.costo_envio'=>'Costo de envío',
			'subtotal'=>'Subtotal',
			'v.total'=>'Total',
			'v.descuento'=>'Descuento',
			'v.codigo_Cupon'=>'Código de Cupón'
		);
		$Grid->assign('value_orden_campo', array_keys($filtros));
		$Grid->assign('dsc_orden_campo', array_values($filtros));
		$Grid->assign('value_orden', array('ASC', 'DESC'));
		$Grid->assign('dsc_orden', array('Ascendente', 'Descendente'));
		
		// Fecha desde
		$fechaDesde = "";
		if(isset($_POST['fecha_desdeDay']) && isset($_POST['fecha_desdeMonth']) && isset($_POST['fecha_desdeYear']))
			// La recien posteada
			$fechaDesde = "{$_POST['fecha_desdeYear']}-{$_POST['fecha_desdeMonth']}-{$_POST['fecha_desdeDay']}";
		elseif(isset($_SESSION['buscador-ventas']['fecha_desde']))
			// La guardada en la session de busqueda
			$fechaDesde = $_SESSION['buscador-ventas']['fecha_desde'];
		else 
			// La de la primer venta registrada
			$fechaDesde = $this->DB->getOne("SELECT DATE_FORMAT(fecha, '%Y-%m-%d') FROM venta ORDER BY fecha");
		$Grid->assign('fecha_desde', $fechaDesde);
		
		// Fecha hasta
		$fechaHasta = "";
		if(isset($_POST['fecha_hastaDay']) && isset($_POST['fecha_hastaMonth']) && isset($_POST['fecha_hastaYear']))
			// La recien posteada
			$fechaHasta = "{$_POST['fecha_hastaYear']}-{$_POST['fecha_hastaMonth']}-{$_POST['fecha_hastaDay']}";
		elseif(isset($_SESSION['buscador-ventas']['fecha_hasta']))
			// La guardada en la session de busqueda
			$fechaHasta = $_SESSION['buscador-ventas']['fecha_hasta'];
		else 
			// La de la primer venta registrada
			$fechaHasta = date("Y-m-d");
		$Grid->assign('fecha_hasta', $fechaHasta);
		
		// Criterio de ordenacion
		$critOrd = "";
		if(isset($_POST['ORDEN'])){
			$critOrd = $_POST['ORDEN'];
			$Grid->addVariable('ORDEN', $critOrd);
		}
		elseif(isset($_SESSION['buscador-ventas']['ORDEN'])){
			$critOrd = $_SESSION['buscador-ventas']['ORDEN'];
		}
		else {
			$critOrd = "DESC";
		}
		$Grid->assign('ORDEN', $critOrd);
		
		$ordCmp = "";
		if(isset($_POST['ORDEN_CAMPO'])){
			$ordCmp = $_POST['ORDEN_CAMPO'];
		}
		elseif(isset($_SESSION['buscador-ventas']['ORDEN_CAMPO'])){
			$ordCmp = $_SESSION['buscador-ventas']['ORDEN_CAMPO'];
		}
		else {
			$ordCmp = "v.fecha";
		}
		$Grid->assign('ORDEN_CAMPO', $ordCmp);
		
		// devuelvo
		return $Grid->fetchGrid(
			'ventas/ventas-grid.htm', 'VENTAS', 
			basename($_SERVER['SCRIPT_NAME']), // Paginador
			"", // PDF
			basename($_SERVER['SCRIPT_NAME']), // Home
			basename($_SERVER['SCRIPT_NAME']), // Mto
			$this->AccionesGrid
		);
	}

	// ------------------------------------------------
	// Genera Formulario
	// ------------------------------------------------
	function _Frm($Accion){
		// Conexion
		$Cnx = $this->DB;
		
		// Formulario
		$Form = new nyiHTML('ventas/venta-detalle.htm');
		$Form->assign('ACC', $Accion);
		$Form->assign('ERROR',$this->Error);

		// Datos
		$Form->assign('id_venta', $this->Registro['id_venta']);
		$Form->assign('fecha', FormatDateLong($this->Registro['fecha']));
		$Form->assign('medio_pago', $this->Registro['medio_pago']);
		$Form->assign('cuotas_oca', $this->Registro['cuotas_oca']);
		$Form->assign('estado_pago', $this->Registro['estado_pago']);
		$Form->assign('estado', $this->Registro['estado']);
		$Form->assign('invitado', $this->Registro['invitado']);
		$Form->assign('subtotal', number_format($this->Registro['total']-$this->Registro['costo_envio'], 2));
		$Form->assign('costo_envio', $this->Registro['costo_envio']);
		$Form->assign('total', $this->Registro['total']);
		$Form->assign('descuento', $this->Registro['descuento']);
		$Form->assign('codigo_Cupon', $this->Registro['codigo_Cupon']);
		
		// Combo estados
		$arrEstados = array('iniciada', 'enviada', 'completa');
		$Form->assign('ids_estados', $arrEstados);
		$Form->assign('dsc_estados', array_map('strtoupper', $arrEstados));
		
		// Comprador invitado o registrado?
		if($this->Registro['invitado']){
			$invitado = new Invitado($Cnx);
			$idInvitado = $this->obtenerIdInvitado($this->Registro['id_venta']);
			$nombreInvitado = $invitado->getNombre($idInvitado);
			$apellidoInvitado = $invitado->getApellido($idInvitado);
			$Form->assign('nombre_comprador', "$nombreInvitado $apellidoInvitado");
			$Form->assign('url_comprador', "reportes.php?MOD=invitados&ACC=C&COD=$idInvitado");
		}
		else {
			$comprador = new Comprador($Cnx);
			$idComprador = $this->obtenerIdComprador($this->Registro['id_venta']);
			$nombreComprador = $comprador->getNombre($idComprador);
			$apellidoComprador = $comprador->getApellido($idComprador);
			$Form->assign('nombre_comprador', "$nombreComprador $apellidoComprador");
			$Form->assign('url_comprador', "reportes.php?MOD=compradores&ACC=C&COD=$idComprador");
		}
		
		$detalles = $this->obtenerDetalles($this->Registro['id_venta']);
		$Form->assign('items', $detalles);
		
		if($Accion == ACC_BAJA || $Accion == ACC_CONSULTA){
			// Si es una baja o consulta, no dejar editar
			$Form->assign('SOLO_LECTURA', 'readonly');
		}
		
		// Script Post
		$Form->assign('SCRIPT_POST',basename($_SERVER['SCRIPT_NAME']).$Form->fetchParamURL($_GET));
	
		// Cabezal
		$Cab = new nyiHTML('base_cabezal_abm.htm');
		$Cab->assign('NOMFORM', 'VENTAS');
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
		
		$this->Ajax->setRequestURI(DIR_HTTP.'ventas/ajax-ventas.php');
		$this->Ajax->registerFunction("modificarEstado");
	
		// Contenido
		return($Form->fetchHTML());
	}
	
	// ------------------------------------------------
	// Cargo campos desde la base de datos
	// ------------------------------------------------
	function _GetDB($Cod=-1, $Campo='id_venta'){
		// Cargo campos
		$this->Registro[$Campo] = $Cod;
		$this->TablaDB->getRegistro($this->Registro, $Campo);
	}
	
	// ------------------------------------------------
	// Cargo campos desde el formulario
	// ------------------------------------------------
	function _GetFrm(){
		// Cargo desde el formulario
		$id = $_POST['id_venta'];
		$this->Registro['id_venta'] = $id;
		$this->Registro['nombre'] = $_POST['nombre'];
		$this->Registro['apellido'] = $_POST['apellido'];
		$this->Registro['email'] = $_POST['email'];
		$this->Registro['direccion'] = $_POST['direccion'];
		$this->Registro['id_departamento'] = $_POST['id_departamento'];
		$this->Registro['ciudad'] = $_POST['ciudad'];
		$this->Registro['codigo_postal'] = $_POST['codigo_postal'];
		$this->Registro['telefono'] = $_POST['telefono'];
	}
	
	function getLastId(){
		return $this->DB->getOne("SELECT max(id_venta) FROM venta");
	}

	// Retorna el combo de identificadores ordenados segun nombre
	function getComboIds($Todos=false, $IdT=0){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT id_venta FROM venta ORDER BY CONCAT(CONCAT(nombre, ' '), apellido)");
		
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($IdT),$Col);
		}
		return($Col);
	}
	
	// ------------------------------------------------
	// Devuelvo array de deventas para combo
	// ------------------------------------------------
	function getComboNombres($Todos=false,$NomT='Todos'){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT CONCAT(CONCAT(nombre, ' '), apellido) FROM venta ORDER BY CONCAT(CONCAT(nombre, ' '), apellido)");
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($NomT),$Col);
		}
		return($Col);
	}
	
	function obtenerVentas(){
		return iterator_to_array($this->DB->execute("SELECT * FROM venta ORDER BY CONCAT(CONCAT(nombre, ' '), apellido)"));
	}
	
	function beforeDelete($id){
		$cntProds = $this->DB->getOne("SELECT COUNT(*) FROM prenda_venta WHERE id_venta = $id");
		if($cntProds > 0){
			$this->Error .= "El venta tiene $cntProds ventas asociadas. ";
		}
	}
	
	function insertar(){
		$res = $this->TablaDB->addRegistro($this->Registro);
		if($res == ''){
			// Si el registro fue insertado correctamente, le seteamos el id_venta
			$this->Registro["id_venta"] = $this->getLastId();
		}
		return $res;
	}
	
	function asociarComprador($comprador){
		$this->comprador__r->Registro["id_venta"] = $this->Registro["id_venta"];
		$this->comprador__r->Registro["id_comprador"] = $comprador;
		return $this->comprador__r->TablaDB->addRegistro($this->comprador__r->Registro);
	}
	
	// $datosItem es del tipo de datos ItemCompra
	function asociarItem($idItem, $datosItem){
		// Inserta el item
		$this->item__r->Registro["id_venta"] = $this->Registro["id_venta"];
		$this->item__r->Registro["item"] = $idItem;
		$this->item__r->Registro["id_prenda"] = $datosItem["id_prenda"];
		$this->item__r->Registro["id_color"] = $datosItem["id_color"];
		$this->item__r->Registro["id_talle"] = $datosItem["id_talle"];
		$this->item__r->Registro["cantidad"] = $datosItem["cantidad"];
		$this->item__r->Registro["precio"] = $datosItem["precio"];
		$this->item__r->Registro["subtotal"] = $datosItem["subtotal"];
		$resAddItem = $this->item__r->TablaDB->addRegistro($this->item__r->Registro);
		if($resAddItem != ""){
			LogArchivo("Error creando item: $resAddItem");
			return $resAddItem;
		}
		else {
			// Actualiza el stock
			$sqlUpdate = "UPDATE prenda_stock ";
			$sqlUpdate .= "SET cantidad = (cantidad - {$datosItem["cantidad"]}) ";
			$sqlUpdate .= "WHERE id_prenda = {$datosItem["id_prenda"]} AND id_talle = {$datosItem["id_talle"]} AND id_color = {$datosItem["id_color"]}";
			$ok = $this->DB->execute($sqlUpdate);
			if($ok === false){
				LogArchivo("Fallo la siguiente consulta tratando de actualizar stock:\n$sqlUpdate");
				return $this->DB->ErrorMsg();
			}
		}
		return "";
	}
	
	function editar(){
		return $this->TablaDB->editRegistro($this->Registro, 'id_venta');
	}
	
	function obtenerDatosCabezal($idVenta){
		return $this->DB->execute("SELECT * FROM venta WHERE id_venta = $idVenta");
	}
	
	function obtenerIdComprador($idVenta){
		return $this->DB->getOne("SELECT id_comprador FROM venta_comprador WHERE id_venta = $idVenta");
	}
	
	function obtenerIdInvitado($idVenta){
		return $this->DB->getOne("SELECT id_invitado FROM invitado WHERE id_venta = $idVenta");
	}
	
	function obtenerDetalles($idVenta){
		$q = "";
		$q .= "SELECT d.item, d.id_prenda, d.id_talle, d.id_color, d.cantidad, d.precio, d.subtotal, p.nombre_prenda, c.nombre_color, t.codigo "; 
		$q .= "FROM venta_item d INNER JOIN prenda p ON p.id_prenda = d.id_prenda INNER JOIN talle t ON t.id_talle = d.id_talle INNER JOIN color c ON c.id_color = d.id_color ";
		$q .= "WHERE d.id_venta = $idVenta ORDER BY item";
		return $this->DB->execute($q);
	}
	
	function cancelar($idVenta, $token){
		$sql = "UPDATE venta SET estado_venta = 'cancelada' WHERE id_venta = $idVenta AND codigo_cancelacion = '$token'";
		$ok = $this->DB->execute($sql);
		if($ok === false){
			return "{$this->DB->ErrorMsg()}\nSQL: $sql";
		}
		return "";
	}
	
	// Sea comprador o invitado, se obtiene el identificador del mismo
	function obtenerIdCliente($idVenta){
		$sql  = "SELECT v.invitado, i.id_invitado, c.id_comprador ";
		$sql .= "FROM venta v LEFT OUTER JOIN invitado i ON i.id_venta = v.id_venta LEFT OUTER JOIN venta_comprador c ON c.id_venta = v.id_venta ";
		$sql .= "WHERE v.id_venta = $idVenta";
		$vectorInfo = iterator_to_array($this->DB->execute($sql));
		$vectorInfo = $vectorInfo[0];
		
		if($vectorInfo['invitado']){
			// Es invitado 
			return (int)$vectorInfo['id_invitado'];
		}
		else {
			// Es comprador 
			return (int)$vectorInfo['id_comprador'];
		}
	}
	
	function confirmarPago($idVenta){
		$sql = "UPDATE venta SET estado_pago = 'confirmado' WHERE id_venta = $idVenta";
		$ok = $this->DB->execute($sql);
		if($ok === false){
			return "{$this->DB->ErrorMsg()}\nSQL: $sql";
		}
		return "";
	}
	
	function asociarCobroAbitab($idVenta, $agencia, $subAgente, $fechaCobro){
		$this->cobro_abitab__r->Registro["id_venta"] = $idVenta;
		$this->cobro_abitab__r->Registro["codigo_agencia"] = $agencia;
		$this->cobro_abitab__r->Registro["codigo_subagente"] = $subAgente;
		$this->cobro_abitab__r->Registro["fecha_cobro"] = $fechaCobro;
		$this->cobro_abitab__r->Registro["fecha_procesado"] = date("Y-m-d H:i");
		return $this->cobro_abitab__r->TablaDB->addRegistro($this->cobro_abitab__r->Registro);
	}
	
	function obtenerCobroAbitab($idVenta){
		$sql = "SELECT * FROM venta_cobro_abitab WHERE id_venta = $idVenta";
		$arr = iterator_to_array($this->DB->execute($sql));
		if(count($arr) > 0){
			return $arr[0];
		}
		else {
			return "";
		}
	}
	
	function modificarEstado($idVenta, $estado){
		$sql = "UPDATE venta SET estado = '$estado' WHERE id_venta = $idVenta";
		$ok = $this->DB->execute($sql);
		if($ok === false){
			return "{$this->DB->ErrorMsg()}\nSQL: $sql";
		}
		return "";
	}
}
?>