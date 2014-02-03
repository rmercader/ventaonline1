<?PHP

include_once('../../app.config.php');
include_once(DIR_LIB . 'nyiDATA.php');
include_once(DIR_LIB . 'nusoap/nusoap.php');
include_once(DIR_BASE . 'funciones-auxiliares.php');
include_once(DIR_BASE . 'seguridad/usuario.class.php');
include_once(DIR_BASE . 'ventas/venta.class.php');

// Inicio Session
session_start();

// Inicio Web Service
$server = new soap_server();

// Configuro WSDL
$server->configureWSDL('procesador-abitab', 'uri:procesador-abitab'); 

// Registro metodo procesarArchivo
$server->register('procesarArchivo', 		// nombre del metodo
	array('contenidoArchivo'=>'xsd:string'),// parametros de entrada
	array('return' => 'xsd:string'),      	// parametros de salida
    'uri:procesador-abitab',                // namespace
    'uri:procesador-abitab/procesarArchivo',// soapaction
    'rpc',                                	// style
    'encoded',                            	// use
    'Procesa el archivo resumen diario.'    // documentation
);

// Registro metodo login
$server->register('login',
	array('user'=>'xsd:string', 'pass'=>'xsd:string'),
	array('return' => 'xsd:int'),
    'uri:procesador-abitab',
    'uri:procesador-abitab/login',
    'rpc',
    'encoded',
    'Inicio de sesion en el servicio. Retorna id del usuario si se pudo iniciar sesion'
);

function procesarArchivo($contenidoArchivo) {
	/* Conexion con la base de datos */
    $Cnx = nyiCNX();
	$Cnx->debug = false;
	
	// Validaciones antes de procesar
	// (1) Lo primero, chequear que exista una session
	if (!isset($_SESSION["activa"])){
	   return new soap_fault('SERVER', '', 'Para ejecutar el procesamiento del archivo se debera iniciar sesion primero.');
	}
	else {
		// Obtengo el ID del usuario de la session y lo valido
		$idUsuario = $_SESSION["cfgusu"]["id_usuario"];
		$clsUsuarios = new Usuario($Cnx);
		$regUsuario = $clsUsuarios->obtenerPorId($idUsuario);
		if(!is_array($regUsuario) || $regUsuario['activo'] == 0){
			return new soap_fault('SERVER', '', 'El usuario de la sesion no existe o no esta activo.');
		}
	}
	
	$length = strlen($contenidoArchivo);
	// (2) Archivo no sea vacio
	if($length == 0){
		return new soap_fault('SERVER', '', 'El archivo recibido es vacio');
	}
	
	// (3) Cantidad de registros sea multiplo de 73
	if($length % 73 != 0){
		$msg = "El archivo recibido es invalido.\n";
		$msg .= "Alguno de los registros esta incompleto.\n";
		$msg .= "El archivo contiene $length caracteres.";
		return new soap_fault('SERVER', '', $msg);
	}
	
	// Englobamos la operacion en una transaccion
	$Cnx->StartTrans();
	
	// Parto el archivo en registros, cada registro es de largo 73
	$regsRecibidos = str_split($contenidoArchivo, 73);
	$i = 0;
	$procesar = true;
	$cantProcesar = count($regsRecibidos);
	$error = '';
	while($i < $cantProcesar && $procesar){
		$regActual = $regsRecibidos[$i];
		// $regActual[0..48]: La concatenacion del codigo de barras de 25 con el de 24
		// $regActual[49..59]: Importe realmente cobrado
		// $regActual[60..61]: Codigo de Agencia Abitab que realizo el cobro
		// $regActual[62..64]: Codigo de Sub-Agente Abitab que realizo el cobro
		// $regActual[65..72]: Fecha de cobro, en formato ddmmaaaa
		LogArchivo("Procesando archivo abitab, registro:\n$regActual"); // Logueo el procesamiento actual
		$codigosBarras = substr($regActual, 0, 49);
		$resImporteRealmenteCobrado = substr($regActual, 49, 11);
		$resCodAgencia = substr($regActual, 60, 2);
		$resCodSubAgente = substr($regActual, 62, 3);
		$resFechaCobro = substr($regActual, 65, 8);
		
		// (4) Valido que el registro pertenezca a PRILI:
		$codClienteAbitab = substr($codigosBarras, 0, 3);
		if($codClienteAbitab != COD_CLIENTE_ABITAB){
			$Cnx->FailTrans(); // Hacemos que la transaccion se anule cuando se llame a CompleteTrans()
			$procesar = false;
			$error = "El registro nro.: " . ($i+1) . " es incorrecto, su identificacion de cliente es distinto a " . COD_CLIENTE_ABITAB . ".";
		}
		else {
			// Hay que desglosar los strings de los codigos de barras
			// $codigosBarras[0..2]: Identificacion de Prili contra Abitab
			// $codigosBarras[3..9]: Identificacion del comprador, relleno con ceros a la izq
			// $codigosBarras[10..16]: Identificacion de la venta, relleno con ceros a la izq
			// $codigosBarras[17..24]: Fecha Vencimiento del Documento en formato ddmmaaaa, no se usa
			// $codigosBarras[25..33]: Importe del Documento, parte entera (9 digitos)
			// $codigosBarras[34..35]: Importe del Documento, parte decimal (2 digitos)
			// $codigosBarras[36]: Moneda del Documento, tiene que ser 1 = $U
			// $codigosBarras[37..38]: Número de Cuota, no se usa
			// $codigosBarras[39]: Tipo de Mora, no se usa
			// $codigosBarras[40]: Tipo de Documento, tiene que ser 1 = FACTURA
			// $codigosBarras[41..47]: Número de Cuenta, no se usa
			// $codigosBarras[48]: Dígito de Control
			
			$idComprador = (int)ltrim(substr($codigosBarras, 3, 7), "0");
			$idVenta = (int)ltrim(substr($codigosBarras, 10, 7), "0");
			$impEnt = (int)ltrim(substr($codigosBarras, 25, 9), "0");
			$impDec = (int)substr($codigosBarras, 34, 2);
			$importe = (float)"$impEnt.$impDec";
			$moneda = (int)substr($codigosBarras, 36, 1);
			$tipoDoc = (int)substr($codigosBarras, 40, 1);
			
			// (5) Valido moneda
			if($moneda != 1){
				$Cnx->FailTrans();
				$procesar = false;
				$error = "El registro nro.: " . ($i+1) . " es incorrecto, la moneda es distinta a " . MONEDA_BASE . ".";
			}
			elseif($tipoDoc != 1){
				// (6) Valido tipo de documento
				$Cnx->FailTrans();
				$procesar = false;
				$error = "El registro nro.: " . ($i+1) . " es incorrecto, el tipo de documento ($tipoDoc) no corresponde a factura.";
			}
			else {
				// Hay que validar (7) que exista la venta, (8) el comprador, (9) que la venta sea del comprador, 
				// (10) que el medio de pago sea ABITAB y (11) que los importes sean correctos.
				$venta = new Venta($Cnx);
				$idCliente = $venta->obtenerIdCliente($idVenta);
				// Aca se ejecutan las validaciones 7, 8 y 9
				if($idCliente != $idComprador){
					$Cnx->FailTrans();
					$procesar = false;
					$error = "El registro nro.: " . ($i+1) . " es incorrecto, no existe la venta de id $idVenta, o no existe el comprador $idComprador, o el comprador no esta asociado a la venta.";
				}
				else {
					
					$arrVenta = iterator_to_array($venta->obtenerDatosCabezal($idVenta));
					$regVenta = $arrVenta[0];
					// (10) que el medio de pago sea ABITAB
					if($regVenta['medio_pago'] != MEDIO_PAGO_ABITAB){
						$Cnx->FailTrans();
						$procesar = false;
						$error = "El registro nro.: " . ($i+1) . " es incorrecto, el medio de pago ({$regVenta['medio_pago']}) no corresponde a ABITAB.";
					}
					else {
						// (11) Ahora los montos...
						$impCobEnt = (int)ltrim(substr($regActual, 49, 9), "0");
						$impCobDec = (int)substr($regActual, 58, 2);
						$importeCobrado = "$impCobEnt.$impCobDec";
						if($importe != $regVenta["total"]){
							// Importe del registro
							$Cnx->FailTrans();
							$procesar = false;
							$error = "El registro nro.: " . ($i+1) . " es incorrecto, el importe recibido ($importe) no corresponde a la factura $idVenta.";
						}
						elseif($importeCobrado != $regVenta["total"]) {
							// Importe cobrado
							$Cnx->FailTrans();
							$procesar = false;
							$error = "El registro nro.: " . ($i+1) . " es incorrecto, el importe cobrado ($importeCobrado) no corresponde a la factura $idVenta.";
						}
						else {
							// (12) Que no haya sido ya procesado el pago para esta venta...
							$regPago = $venta->obtenerCobroAbitab($idVenta);
							if(is_array($regPago)){
								$Cnx->FailTrans();
								$procesar = false;
								$error = "El registro nro.: " . ($i+1) . " ya ha sido procesado anteriormente el: {$regPago['fecha_procesado']}.";
							}
							else{
								// El registro paso todas las validaciones, entonces se procede a registrar
								$agencia = substr($regActual, 60, 2); // Agencia
								$subAgente = substr($regActual, 62, 3); // Sub-agente
								$fDia = substr($regActual, 65, 2); // Dia dd cobro
								$fMes = substr($regActual, 67, 2); // Mes mm cobro
								$fAno = substr($regActual, 69, 4); // Año aaaa cobro
								$fechaCobro = "$fAno-$fMes-$fDia";
								
								$error .= $venta->confirmarPago($idVenta);
								$error .= $venta->asociarCobroAbitab($idVenta, $agencia, $subAgente, $fechaCobro);
								
								if($error != ""){
									$Cnx->FailTrans();
									$procesar = false;
								}
								else {
									LogArchivo("Confirmado el pago ABITAB de la venta nro $idVenta.");
									// Registro correctamente
									// Finalmente, avanzo el puntero, no al loop infinito!
									$i++;
								}
							}
						}
					}
				}
			}
		}
	}
	
	// Finalizamos la transaccion
	$Cnx->CompleteTrans();
	
	// Si se encontraron errores en el procesamiento
	if(!$procesar){
		LogArchivo("Error procesando archivo abitab, registro:\n$regActual\nError: $error"); // Logueo el error
		return new soap_fault('SERVER', '', $error);
	}
	else {
		LogArchivo("Procesados exitosamente $i registros.");
		return "";
	}
}

function login($user, $pass){
	$Cnx = nyiCNX();
	$Cnx->debug = false;
	$clsUser = new Usuario($Cnx);
	$UserLogged = $clsUser->Login($user, $pass);
		
	if($UserLogged === false){
		$error = "No se pudo iniciar sesion. Verifique que su usuario y clave sean correctos, o que su cuenta no haya sido desactivada por el administrador del sistema.";
		return new soap_fault('SERVER', '', $error);
	}
	
	$_SESSION["activa"]   = 'S';
	$_SESSION["cfgusu"]["nombre_usuario_admin"] = $UserLogged['nombre_usuario_admin'];
	$_SESSION["cfgusu"]["id_usuario"] = $UserLogged['id_usuario_admin'];
	
	return $_SESSION["cfgusu"]["id_usuario"];
}

// Use the request to (try to) invoke the service
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);

?>