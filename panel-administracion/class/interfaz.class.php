<?PHP 

include_once(DIR_BASE.'prendas/coleccion.class.php');
include_once(DIR_BASE.'prendas/color.class.php');
include_once(DIR_BASE.'prendas/talle.class.php');
include_once(DIR_BASE.'class/class.phpmailer.php');
include_once(DIR_BASE.'class/barcode39.class.php');
include_once(DIR_BASE.'class/departamento.class.php');
include_once(DIR_BASE.'class/foxycart-helper.class.php');
include_once(DIR_BASE.'ventas/comprador.class.php');
include_once(DIR_BASE.'ventas/invitado.class.php');
include_once(DIR_BASE.'ventas/venta.class.php');
include_once(DIR_BASE.'seguridad/seguridad.class.php');

/* Clase de interfaz de la logica de negocio */
class Interfaz {
	
	private $Cnx; // AdoDBConnection
	
	// Constructor
	function Interfaz($conn=false){
		if($conn === false)
			$this->Cnx = nyiCNX();
		else
			$this->Cnx = $conn;
		$this->Cnx->debug = false;
	}
	
	// Inicia una Transaccion contra la base
	function StartTransaction(){
		//$this->Cnx->execute("BEGIN");
		$this->Cnx->StartTrans();
	}
	
	// Realiza COMMIT si no existen errores sql, ROLLBACK en caso contrario
	function CompleteTransaction(){
		/*if($this->Cnx->ErrorMsg() == ''){
			$this->Cnx->execute("COMMIT");
		}
		else{
			LogArchivo("Rollback: {$this->Cnx->ErrorMsg()}");
			$this->Cnx->execute("ROLLBACK");
		}*/
		$this->Cnx->CompleteTrans();
	}
	
	function guardarOrientacionHome($orientacionHome){
		$ok = $this->Cnx->execute("UPDATE configuracion SET orientacion_home = '$orientacionHome'");
		if($ok === false){
			LogArchivo("Error guardando orientacion de la home: " . $this->Cnx->ErrorMsg());
			return "Se produjo un error guardando orientacion de la home.\n";
		}
		return "";
	}
	
	function obtenerOrientacionHome(){
		$orientacion = $this->Cnx->getOne("SELECT orientacion_home FROM configuracion");
		return $orientacion;
	}
	
	function guardarImagenHome($imgHome){
		$ok = move_uploaded_file($imgHome['tmp_name'], DIR_BASE . "parametros/imagen-home.jpg");
		if($ok === false){
			return "Se produjo un error guardando la imagen de la home.\n";
		}
		return "";
	}
	
	function obtenerUrlImagenHome(){
		return DIR_HTTP . "parametros/imagen-home.jpg?t=" . time();
	}
	
	function obtenerCostoEnvio($idDepartamento){
		$obj = new Departamento($this->Cnx);
		return $obj->getCostoEnvio($idDepartamento);
	}
	
	function cancelarVenta($idVenta, $token){
		$venta = new Venta($this->Cnx);
		$res = $venta->cancelar($idVenta, $token);
		if($res == ""){
			// Mail avisando que una compra fue cancelada
			// Creo el mail para prili
			$mailPrili = new PHPMailer();
			$mailPrili->IsHTML(true);
			$mailPrili->IsMail();
			$mailPrili->Host = MAIL_HOST;
			$mailPrili->From = CASILLA_NO_REPLY;
			$mailPrili->FromName = "Prili Venta Online";	
			$mailPrili->Subject = utf8_decode("Notificación de anulación de venta");
			// Destinatario 
			$mailPrili->AddAddress(CASILLA_NOTIFICACION_VENTA);
			$mailPrili->Body = $mailPrili->WrapText("<br /><br />La venta nro.: $idVenta ha sido cancelada por el comprador.", 72);
			$success = $mailPrili->Send();
			if($success === FALSE){
				LogArchivo("No se pudo enviar mail a PRILI por concepto de cancelacion de venta nro.: $idVenta.\nInfo del error: {$mailPrili->ErrorInfo}");	
			}
		}
		return $res;
	}
	
	function obtenerDatosVenta($idVenta){
		$venta = new Venta($this->Cnx);
		$depto = new Departamento($this->Cnx);
		$resCab = iterator_to_array($venta->obtenerDatosCabezal($idVenta));
		if(count($resCab) > 0){
			$datosVenta = array();
			$cabezal = $resCab[0];
			$datosVenta["cabezal"] = $cabezal;
			if($cabezal["invitado"]){
				// Compraron como invitado
				$invitado = new Invitado($this->Cnx);
				$resInv = iterator_to_array($invitado->obtenerDatosPorVenta($idVenta));
				if(count($resInv) > 0){
					$datosVenta["invitado"] = $resInv[0];
					$datosVenta["invitado"]["departamento"] = $depto->getNombre($datosVenta["invitado"]["id_departamento"]);
				}
			}
			else{
				$comprador = new Comprador($this->Cnx);
				$resCmp = iterator_to_array($comprador->obtenerDatos($venta->obtenerIdComprador($idVenta)));
				if(count($resCmp) > 0){
					$datosVenta["comprador"] = $resCmp[0];
					$datosVenta["comprador"]["departamento"] = $depto->getNombre($datosVenta["comprador"]["id_departamento"]);
				}
			}
			$datosVenta["items"] = iterator_to_array($venta->obtenerDetalles($idVenta));
		}
		
		return $datosVenta;
	}
	
	function actualizarDatosComprador($datosComprador){
		$comprador = new Comprador($this->Cnx);
		// Actualizar los datos del comprador
		$comprador->Registro['id_comprador'] = $datosComprador['id_comprador'];
		$comprador->Registro['nombre'] = $datosComprador['nombre'];
		$comprador->Registro['apellido'] = $datosComprador['apellido'];
		$comprador->Registro['direccion'] = $datosComprador['direccion'];
		$comprador->Registro['id_departamento'] = $datosComprador['id_departamento'];
		$comprador->Registro['ciudad'] = $datosComprador['ciudad'];
		$comprador->Registro['codigo_postal'] = $datosComprador['codigo_postal'];
		$comprador->Registro['telefono'] = $datosComprador['telefono'];
		
		// Estos datos no se modifican
		$comprador->Registro['fecha_registrado'] = $comprador->getFechaRegistrado($datosComprador['id_comprador']);
		$comprador->Registro['password'] = $comprador->getPassword($datosComprador['id_comprador']);
		$comprador->Registro['email'] = $comprador->getEmail($datosComprador['id_comprador']);
		$resCmpUpd = $comprador->editar();
		if($resCmpUpd != ""){
			LogArchivo("Error editando comprador: $resCmpUpd");
			return "Sus datos no pudieron ser modificados debido a un error inesperado. Disculpe las molestias ocasionadas.";
		}
		return "";
	}
	
	function registrarVenta($datosComprador, $datosVenta, $infoExtra)
	{
		$res = "";
		$security = new Seguridad($this->Cnx);
		$this->StartTransaction();	
		
		// Chequeos de stock
		$prenda = new Prenda($this->Cnx);
		$noStock = "";
		$totalValidacion = 0;
		foreach($datosVenta["items"] as $item){
			if($this->obtenerCantidadStock($item["id_prenda"], $item["id_color"], $item["id_talle"]) < $item["cantidad"]){
				$noStock .= "{$item['descripcion']}\n";
			}
			$totalValidacion += $item["subtotal"];
		}

		// Finalmente le sumo el costo de envio
		$totalValidacion += $datosVenta["costo_envio"];
		//y le resto el monto de descuento
		$totalValidacion -= $datosVenta["descuento"];

		if($totalValidacion != $datosVenta["total"]){
			$res .= "No coincide la suma de subtotales con el total de la venta. ";
		}
		if($noStock != ""){
			$res .= "No hay stock para los siguientes items:\n$noStock";
		}
		
		if($res == ""){
			$venta = new Venta($this->Cnx);
			
			$tokenCancelacion = $security->generatePassword(10);
			$venta->Registro["codigo_cancelacion"] = $tokenCancelacion;
			$venta->Registro["total"] = $datosVenta["total"];
			$venta->Registro["costo_envio"] = $datosVenta["costo_envio"];
			$venta->Registro["descuento"] = $datosVenta["descuento"];
			$venta->Registro["estado"] = 'iniciada';
			$venta->Registro["fecha"] = date("Y-m-d H:i:00");
			switch ($infoExtra["medio_pago"]) {
				case MEDIO_PAGO_ABITAB:
					$estadoPago = "pendiente";
					break;
				case MEDIO_PAGO_OCA:
					$estadoPago = "confirmado";
					break;
				default:
					$estadoPago = "pendiente";
					break;
			}
			$venta->Registro["estado_pago"] = $estadoPago;
			$venta->Registro["estado_venta"] = "confirmada";
			$venta->Registro["moneda"] = MONEDA_BASE;
			$venta->Registro["medio_pago"] = $infoExtra["medio_pago"];
			$venta->Registro["invitado"] = $infoExtra["invitado"] ? 1 : 0;
			$venta->Registro["cuotas_oca"] = (int) $infoExtra["cuotas_oca"];
			$venta->Registro["nrorsv"] = $infoExtra["nrorsv"];
			$venta->Registro["codigo_Cupon"] = $infoExtra["codigo_Cupon"];
			$resVentaIns = $venta->insertar();
			if($resVentaIns != ""){
				LogArchivo("Error insertando venta: $resVentaIns");
			}
			$idVenta = $venta->Registro['id_venta'];
			$res = $idVenta;
			
			// Si la venta quedo correctamente guardada
			if(is_numeric($idVenta) && $idVenta > 0){
				// Primero verificamos los datos del comprador, si ya existe y si compro como invitado o con su cuenta registrada
				if($infoExtra["invitado"]){
					// Compra como invitado, creo primero la venta
					$invitado = new Invitado($this->Cnx);
					$invitado->Registro['nombre'] = $datosComprador['nombre'];
					$invitado->Registro['apellido'] = $datosComprador['apellido'];
					$invitado->Registro['email'] = $datosComprador['email'];
					$invitado->Registro['direccion'] = $datosComprador['direccion'];
					$invitado->Registro['id_departamento'] = $datosComprador['id_departamento'];
					$invitado->Registro['ciudad'] = $datosComprador['ciudad'];
					$invitado->Registro['codigo_postal'] = $datosComprador['codigo_postal'];
					$invitado->Registro['telefono'] = $datosComprador['telefono'];
					$invitado->Registro['id_venta'] = $idVenta;
					$invitado->Registro["fecha_registrado"] = date("Y-m-d H:i:00");
					$resInvIns = $invitado->insertar();
					if($resInvIns != ""){
						LogArchivo("Error insertando invitado: $resInvIns");
					}
					$idComprador = $invitado->Registro["id_invitado"];
				}
				else{
					// Compra con la cuenta de usuario
					$comprador = new Comprador($this->Cnx);
					$idComprador = $comprador->obtenerIdPorEmail($datosComprador['email']);
					if(!is_numeric($idComprador)){
						// No esta dado de alta, hay que crearlo
						$comprador->Registro['fecha_registrado'] = date("Y-m-d H:i:00");
						$comprador->Registro['nombre'] = $datosComprador['nombre'];
						$comprador->Registro['apellido'] = $datosComprador['apellido'];
						$comprador->Registro['email'] = $datosComprador['email'];
						$comprador->Registro['direccion'] = $datosComprador['direccion'];
						$comprador->Registro['id_departamento'] = $datosComprador['id_departamento'];
						$comprador->Registro['ciudad'] = $datosComprador['ciudad'];
						$comprador->Registro['codigo_postal'] = $datosComprador['codigo_postal'];
						$comprador->Registro['telefono'] = $datosComprador['telefono'];
						$comprador->Registro['password'] = $security->Encriptar($datosComprador['clave']);
						$resCmpIns = $comprador->insertar();
						if($resCmpIns != ""){
							LogArchivo("Error insertando comprador: $resCmpIns");
						}
						$idComprador = $comprador->Registro["id_comprador"];
					}
					else{
						// Actualizar los datos del comprador
						$comprador->Registro['id_comprador'] = $idComprador;
						$comprador->Registro['email'] = $datosComprador['email'];
						$comprador->Registro['nombre'] = $datosComprador['nombre'];
						$comprador->Registro['apellido'] = $datosComprador['apellido'];
						$comprador->Registro['direccion'] = $datosComprador['direccion'];
						$comprador->Registro['id_departamento'] = $datosComprador['id_departamento'];
						$comprador->Registro['ciudad'] = $datosComprador['ciudad'];
						$comprador->Registro['codigo_postal'] = $datosComprador['codigo_postal'];
						$comprador->Registro['telefono'] = $datosComprador['telefono'];
						
						// Estos datos no se modifican
						$comprador->Registro['fecha_registrado'] = $comprador->getFechaRegistrado($idComprador);
						$comprador->Registro['password'] = $comprador->getPassword($idComprador);
						$resCmpUpd = $comprador->editar();
						if($resCmpUpd != ""){
							LogArchivo("Error editando comprador: $resCmpUpd");
						}
					}
					
					if(intval($idComprador) > 0){
						// Asociar comprador a la venta
						$resCmpAsoc = $venta->asociarComprador($idComprador);
						if($resCmpAsoc != ""){
							LogArchivo("Error asociando comprador: $resCmpAsoc");
						}
					}
				}
				
				// Se asocio correctamente un comprador, proseguimos
				if($idComprador > 0){
					// Insertamos los items
					$i = 1;
					$resItemAsoc = '';
					foreach($datosVenta["items"] as $item){
						$resItemAsoc .= $venta->asociarItem($i, $item);
						if($resItemAsoc != ""){
							LogArchivo("Error asociando item: $resItemAsoc");
							break;
						}
						$i++;
					}

					//marcamos el codigo de cupon como utilizado...
					$auxCodCupon = $infoExtra["codigo_Cupon"];
					$sqlUpdate = "UPDATE cupon_codigo SET utilizado = 1 where codigo = '$auxCodCupon'";
					$ok = $this->Cnx->execute($sqlUpdate);
					if($ok === false){
						LogArchivo("Fallo la siguiente consulta tratando de actualizar stock:\n $sqlUpdate");
					}

					//envio los mails...
					if($resItemAsoc == ''){
						// Envio del mail al comprador y luego a cuenta de Prili
						$depto = new Departamento($this->Cnx);
						$htmlMail = new nyiHTML("ventas/email-receipt.htm", DIR_HTML_ADMIN);
						
						// Datos de la compra
						$htmlMail->assign('id_venta', $idVenta);
						$htmlMail->assign('fecha', FormatDateLong($venta->Registro["fecha"]));
						
						// Dependiendo del medio de pago...
						$strInfoPago = "";
						switch($infoExtra["medio_pago"]){
							case MEDIO_PAGO_ABITAB:
								$strInfoPago = "AbitabNET";
								break;

							case MEDIO_PAGO_OCA:
								$strInfoPago = "PAGO ONLINE OCA CARD (Plan: {$infoExtra['cuotas_oca']} pagos)";
								break;
						}
						$htmlMail->assign('infoPago', $strInfoPago);
						$htmlMail->assign('estadoPago', strtoupper($venta->Registro["estado_pago"]));
						$htmlMail->assign('estadoVenta', strtoupper($venta->Registro["estado_venta"]));
						
						$subtotalDsc = number_format($datosVenta["total"]-$datosVenta["costo_envio"]+$datosVenta["descuento"], 2);
						$costoDsc = number_format($datosVenta["costo_envio"], 2);
						$descuentoDsc = number_format($datosVenta["descuento"], 2);
						$totalDsc = number_format($datosVenta["total"], 2);
						$tablaDetalles = new nyiHTML("ventas/tabla-detalles.htm", DIR_HTML_ADMIN);
						$tablaDetalles->assign("subtotal", $subtotalDsc);
						$tablaDetalles->assign("costo_envio", $costoDsc);
						$tablaDetalles->assign("valorDescuento", $descuentoDsc);
						$tablaDetalles->assign("total",  $totalDsc);
						$detalles = array();
						$detallesVenta = iterator_to_array($venta->obtenerDetalles($idVenta));
						foreach($detallesVenta as $item){
							array_push($detalles, array(
								'nombre'=>$item['nombre_prenda'],
								'talle'=>$item['codigo'],
								'color'=>$item['nombre_color'],
								'cantidad'=>$item['cantidad'],
								'unitario'=>$item['precio'],
								'total'=>$item['subtotal']
							));
						}
						
						$tablaDetalles->assign("items", $detalles);
						$htmlMail->assign('detallesVenta', $tablaDetalles->fetchHTML());
						$htmlMail->assign("subtotal", $subtotalDsc);
						$htmlMail->assign("costo_envio", $costoDsc);
						$htmlMail->assign("descuento", $descuentoDsc);
						$htmlMail->assign("total",  $totalDsc);
						$htmlMail->assign("mensaje", "Estimado <b>{$datosComprador["nombre"]}</b><br />Muchas gracias por tu compra.<br />");
						
						// Datos del comprador
						$htmlMail->assign('nombre', $datosComprador["nombre"]);
						$htmlMail->assign('apellido', $datosComprador["apellido"]);
						$htmlMail->assign('email', $datosComprador["email"]);
						$htmlMail->assign('direccion', $datosComprador["direccion"]);
						$htmlMail->assign('telefono', $datosComprador["telefono"]);
						$htmlMail->assign('ciudad', $datosComprador["ciudad"]);
						$htmlMail->assign('departamento', $depto->getNombre($datosComprador["id_departamento"]));
						$htmlMail->assign('codigo_postal', $datosComprador["codigo_postal"]);
						
						// Creo ambos contenidos HTML para cada mail
						//$htmlMail->assign("urlAnulacion", DIR_HTTP_PUBLICA . "cancelar-compra.php?id=$idVenta&token=$tokenCancelacion"); Queda inhabilitado de momento
						
						// Talon ABITAB
						if($infoExtra["medio_pago"] == MEDIO_PAGO_ABITAB){
							$htmlMail->assign("talonAbitab", _SI);
						}

						$contComprador = $htmlMail->fetchHTML();
						
						$htmlMail->assign("mensaje", "Se ha generado una nueva venta");
						$htmlMail->assign("urlAnulacion", "");
						$htmlMail->assign("talonAbitab", "");
						
						$contPrili = $htmlMail->fetchHTML();
						
						// Creo el mail para el comprador
						$mailComprador = new PHPMailer();
						$mailComprador->IsHTML(true);
						$mailComprador->IsMail();
						$mailComprador->Host = MAIL_HOST;
						$mailComprador->From = CASILLA_NO_REPLY;
						$mailComprador->FromName = "Prili";	
						$mailComprador->Subject = "Orden de compra nro.: $idVenta";
						// Destinatario 
						$mailComprador->AddAddress($datosComprador['email']);
						$mailComprador->AddBCC("mcaravia@narthex.com.uy");
						$mailComprador->Body = utf8_decode($mailComprador->WrapText($contComprador, 72));
						LogArchivo(utf8_decode($mailComprador->WrapText($contComprador, 72)));
						$success = $mailComprador->Send();
						if($success === FALSE){
							LogArchivo("No se pudo enviar mail al comprador por concepto de venta nro.: $idVenta.\nInfo del error: {$mailComprador->ErrorInfo}");	
						}
						
						// Creo el mail para prili
						$mailPrili = new PHPMailer();
						$mailPrili->IsHTML(true);
						$mailPrili->IsMail();
						$mailPrili->Host = MAIL_HOST;
						$mailPrili->From = CASILLA_NO_REPLY;
						$mailPrili->FromName = "Prili Venta Online";	
						$mailPrili->Subject = "Se ha generado una nueva venta con nro.: $idVenta";
						// Destinatario 
						$mailPrili->AddAddress(CASILLA_NOTIFICACION_VENTA);
						$mailPrili->AddBCC("mcaravia@narthex.com.uy");
						$mailPrili->AddBCC("ventasonline@prili.net");
						$mailPrili->AddBCC("anamaria@prili.net");
						$mailPrili->AddBCC("ana@prili.net");
						$mailPrili->Body = utf8_decode($mailPrili->WrapText($contPrili, 72));
						//LogArchivo(utf8_decode($mailPrili->WrapText($contPrili, 72)));
						$success = $mailPrili->Send();
						if($success === FALSE){
							LogArchivo("No se pudo enviar mail a PRILI por concepto de venta nro.: $idVenta.\nInfo del error: {$mailPrili->ErrorInfo}");	
						}
					}
					else {
						// Manejo condicion de error
						LogArchivo("No se pudo ingresar el registro de venta: $idVenta, hubieron errores al asociar items.");
						$res = "Ocurrio un error inesperado y no se pudo completar la compra.";
					}
				}
			}
			else { 
				// Manejo condicion de error
				LogArchivo("No se pudo ingresar el registro de venta: $idVenta");
				$res = "Ocurrio un error inesperado y no se pudo completar la compra.";
			}
		}
		$this->CompleteTransaction();
		return $res;
	}
	
	function obtenerIdsDepartamentos(){
		$oDepto = new Departamento($this->Cnx);
		return $oDepto->GetComboIds(true);
	}
	
	function obtenerDscDepartamentos(){
		$oDepto = new Departamento($this->Cnx);
		return $oDepto->GetComboNombres(true, 'Seleccionar');
	}
	
	function obtenerConversionMoneda(){
		$res = $this->Cnx->execute("SELECT conversion_moneda, DATEDIFF(NOW(), ultima_actualizacion_conversion_moneda) AS diasValor FROM configuracion");
		$convRateMatrix = iterator_to_array($res);
		if($convRateMatrix[0]["conversion_moneda"] <= 0 || (int)$convRateMatrix[0]["diasValor"] >= 1){
			$rate = $this->consultarServicioConversionMonedaPrincipal();
			if(is_numeric($rate)){
				$this->Cnx->execute("UPDATE configuracion SET conversion_moneda = $rate, ultima_actualizacion_conversion_moneda = NOW()");
				LogArchivo("SE ACTUALIZO LA CONVERSION DE LA MONEDA AL VALOR: $rate");
				if($convRateMatrix[0]["conversion_moneda"] <= 0){
					LogArchivo("RAZON: Conversion menor o igual a 0.");
				}
				if((int)$convRateMatrix[0]["diasValor"] >= 1){
					LogArchivo("RAZON: La ultima actualizacion fue hecha hace mas de un dia.");
				}
				$convRate = $rate;
			}
			else {
				LogArchivo("El servicio de conversion de moneda no esta operando, o esta retornando un valor incorrecto.");
				if($convRateMatrix[0]["conversion_moneda"] <= 0){
					die("No se pudieron obtener los datos necesarios para la prenda. Disculpe los inconvenientes.");
				}
				else {
					$convRate = $convRateMatrix[0]["conversion_moneda"];
				}
			}
		}
		else {
			$convRate = $convRateMatrix[0]["conversion_moneda"];
		}
		return $convRate;
	}
	
	function consultarServicioConversionMonedaPrincipal(){
		// Include de funcionalidad para consumir Web Services SOAP
		require_once(DIR_LIB . "nusoap/nusoap.php");
		$client = new nusoap_client(URL_WS_COTIZACION, true);
		$err = $client->getError();
		if($err){
			LogError(htmlspecialchars($client->getDebug(), ENT_QUOTES), __FILE__, "consultarServicioConversionMonedaPrincipal()");
			return "No se pudo obtener la cotizacion de la moneda " . MONEDA_TRANSACCIONES;
		}
		else{
			$params = array(
				'FromCurrency'=>MONEDA_BASE,
				'ToCurrency'=>MONEDA_TRANSACCIONES
			);
			$result = $client->call("ConversionRate", $params);
			if ($client->fault) {
				LogError(print_r($result, true), __FILE__, "consultarServicioConversionMonedaPrincipal()");
				return "No se pudo obtener la cotizacion de la moneda " . MONEDA_TRANSACCIONES;
			} 
			else {
				$err = $client->getError();
				if ($err) {
					LogError($err, __FILE__, "consultarServicioConversionMonedaPrincipal()");
					return "No se pudo obtener la cotizacion de la moneda " . MONEDA_TRANSACCIONES;
				} 
				else {
					return $result["ConversionRateResult"];
				}
			}
		}
	}
	
	/**
	 * Raw HTML Signing: Sign all links and form elements in a block of HTML
	 *
	 * Accepts a string of HTML and signs all links and forms.
	 * Requires link 'href' and form 'action' attributes to use 'https' and not 'http'.
	 * Requires a 'code' to be set in every form.
	 *
	 * @return string
	 **/
	function firmarHTML($html){
		return FoxyCartHelper::fc_hash_html($html);
	}
	
	function obtenerCantidadStock($idPrenda, $idColor, $idTalle){
		$obj = new Prenda($this->Cnx);
		return $obj->obtenerCantidadStock($idPrenda, $idColor, $idTalle);
	}
	
	function obtenerPrecioPrenda($idPrenda){
		$obj = new Prenda($this->Cnx);
		return $obj->getPrecio($idPrenda);
	}
	
	function obtenerColoresPrenda($idPrenda){
		$obj = new Prenda($this->Cnx);
		$objColor = new Color($this->Cnx);
		$lstColores = iterator_to_array($obj->obtenerColores($idPrenda));
		for($i = 0; $i < count($lstColores); $i++){
			$lstColores[$i]["foto"] = $objColor->getUrlImagen($lstColores[$i]['id_color']);
			$lstColores[$i]["thumbnail"] = $objColor->getUrlImagen($lstColores[$i]['id_color'], 1);
		}
		return $lstColores;
	}
	
	function obtenerTallesPrenda($idPrenda){
		$obj = new Prenda($this->Cnx);
		return $obj->obtenerTallesAsociados($idPrenda);
	}
	
	function obtenerDatosPrenda($idPrenda){
		$obj = new Prenda($this->Cnx);
		return $obj->obtenerDatos($idPrenda);
	}
	
	function obtenerFotosPrenda($idPrenda){
		$obj = new Prenda($this->Cnx);
		$lstFotos = iterator_to_array($obj->obtenerGaleriaFotos($idPrenda));
		$lstUrls = array();
		foreach($lstFotos as $foto){
			$extension = $foto['extension'];
			$nomSinExt = $foto['nombre_imagen']; 
			array_push($lstUrls, DIR_HTTP_FOTOS_PRENDAS . "$idPrenda/$nomSinExt.$extension");
		}
		return $lstUrls;
	}
	
	function obtenerPrendasDestacadas(){
		$objPrenda = new Prenda($this->Cnx);
		return $objPrenda->obtenerListadoDestacadas();
	}
	
	function obtenerPrendasPorCategoria($idCategoria, $pagina){
		$objPrenda = new Prenda($this->Cnx);
		return $objPrenda->obtenerListadoPorCategoria($idCategoria, $pagina * PRENDAS_POR_PAGINA);
	}
	
	function obtenerPrendasPorColeccion($idColeccion, $pagina){
		$objPrenda = new Prenda($this->Cnx);
		return $objPrenda->obtenerListadoPorColeccion($idColeccion, $pagina * PRENDAS_POR_PAGINA);
	}
	
	function obtenerDatosCategoria($idCategoria, $campos=null){
		$objCat = new CategoriaPrenda($this->Cnx);
		return $objCat->obtenerDatos($idCategoria, $campos);
	}
	
	function obtenerUrlFotoCategoriaPrendas($idCategoria){
		$objCat = new CategoriaPrenda($this->Cnx);
		return $objCat->getUrlFoto($idCategoria);
	}
	
	function obtenerUrlFotoColeccionPrendas($idColeccion){
		$objCol = new Coleccion($this->Cnx);
		return $objCol->getUrlFoto($idColeccion);
	}
	
	function obtenerSubcategorias($idCategoria, $campos=null){
		$objCat = new CategoriaPrenda($this->Cnx);
		return $objCat->obtenerSubcategorias($idCategoria, $campos=null);
	}
	
	function obtenerCategoriasPrincipalesPorLinea($idLinea, $campos=null){
		$objCat = new CategoriaPrenda($this->Cnx);
		return $objCat->categoriasPrincipalesPorLinea($idLinea, $campos);
	}
	
	function obtenerCategoriasConPrendasPorLinea($idLinea, $campos=null){
		$objCat = new CategoriaPrenda($this->Cnx);
		return $objCat->categoriasConPrendasPorLinea($idLinea, $campos);
	}
	
	function obtenerColeccionesConPrendasPorLinea($idLinea, $campos=null){
		$objCol = new Coleccion($this->Cnx);
		return $objCol->coleccionesConPrendasPorLinea($idLinea, $campos);
	}
	
	function obtenerDatosColeccion($idColeccion, $campos=null){
		$objCol = new Coleccion($this->Cnx);
		return $objCol->obtenerDatos($idColeccion, $campos);
	}
	
	function obtenerNombreLinea($idLinea){
		$datos = "";
		switch($idLinea){
			case LINEA_DAMA:
				$datos = "Dama";
				break;
			case LINEA_HOMBRE:
				$datos = "Hombre";
				break;
			case LINEA_INFANTIL:
				$datos = "Infantil";
				break;
		}
		return $datos;
	}
	
	function obtenerTalles(){
		$obj = new Talle($this->Cnx);
		return $obj->obtenerTalles();
	}
	
	function obtenerIdTallePorCodigo($codTalle){
		$obj = new Talle($this->Cnx);
		return $obj->obtenerIdPorCodigo($codTalle);
	}
	
	function obtenerColores(){
		$obj = new Color($this->Cnx);
		$lstColores = $obj->obtenerColores();
		for($i = 0; $i < count($lstColores); $i++){
			$lstColores[$i]["foto"] = $obj->getUrlImagen($lstColores[$i]['id_color']);
			$lstColores[$i]["thumbnail"] = $obj->getUrlImagen($lstColores[$i]['id_color'], 1);
		}
		return $lstColores;
	}
	
	function esEmailValido($email){
		if(preg_match('/^[_\x20-\x2D\x2F-\x7E-]+(\.[_\x20-\x2D\x2F-\x7E-]+)*@(([_a-z0-9-]([_a-z0-9-]*[_a-z0-9-]+)?){1,63}\.)+[a-z0-9]{2,6}$/i', $email)){
			return TRUE;
		}
		return FALSE;
	}
	
	function loguearError($mensaje, $operacion='', $adicional=''){
		$logFilePtr = fopen(LOG_ERRORES, "a+");
		$log = sprintf("ERROR: %s\nFECHA: %s\nOPERACIÓN: %s\nINF. ADICIONAL: %s\n\n", $mensaje, date("d-m-Y H:i"), $operacion, $adicional);
		fwrite($logFilePtr, $log);
		fflush($logFilePtr);
		fclose($logFilePtr);
	}
	
	function armarMensajeError($mensaje){
		$contenido = new nyiHTML('base-error.htm');	
		$contenido->assign('error', $mensaje);
		return $contenido->fetchHTML();
	}
	
	function armarMensajeExito($mensaje){
		$contenido = new nyiHTML('base-exito.htm');	
		$contenido->assign('mensaje', $mensaje);
		return $contenido->fetchHTML();
	}
	
	function idCompradorPorEmail($email){
		$comprador = new Comprador($this->Cnx);
		$idComprador = $comprador->obtenerIdPorEmail($email);
		return $idComprador;
	}
	
	function datosCompradorPorEmail($email){
		$comprador = new Comprador($this->Cnx);
		$idComprador = $comprador->obtenerIdPorEmail($email);
		if($idComprador > 0){
			return iterator_to_array($comprador->obtenerDatos($idComprador));
		}
		else {
			return "";
		}
	}
	
	function datosCompradorPorId($idComprador){
		$comprador = new Comprador($this->Cnx);
		if($idComprador > 0){
			$arrIter = iterator_to_array($comprador->obtenerDatos($idComprador));
			return $arrIter[0];
		}
		else {
			return "";
		}
	}
	
	function autenticarComprador($mail, $clave){
		$comprador = new Comprador($this->Cnx);
		$idComprador = $comprador->autenticar($mail, $clave);
		return $idComprador;
	}
	
	function enviarNuevaClave($mail){
		$objComprador = new Comprador($this->Cnx);
		$idComprador = (int)($objComprador->obtenerIdPorEmail($mail));
		// Valido el comprador
		if($idComprador > 0){
			// Genero una nueva contraseña
			$security = new Seguridad($this->Cnx);
			$clave = $security->GenerarPassword();
			$objComprador->setPassword($idComprador, $security->Encriptar($clave));
			
			// La enviamos por mail
			$mailPrili = new PHPMailer();
			$mailPrili->IsHTML(true);
			$mailPrili->IsMail();
			$mailPrili->Host = MAIL_HOST;
			$mailPrili->From = CASILLA_NO_REPLY;
			$mailPrili->FromName = "Prili Venta Online";	
			$mailPrili->Subject = utf8_decode("Nueva contraseña");
			// Destinatario 
			$mailPrili->AddAddress($mail);
			$emailBody = '<br /><img height="53px" src="' . DIR_HTTP_PUBLICA . 'images/logo.png"><br /><br /><br />';
			$emailBody .= "Estimado cliente, su nueva contraseña es: $clave<br />";
			$emailBody .= "Quedamos a su entera disposición, <br />";
			$emailBody .= "PRILI S.A.";
			$mailPrili->Body = $mailPrili->WrapText($emailBody, 72);
			$success = $mailPrili->Send();
			if($success === FALSE){
				LogArchivo("No se pudo enviar mail a $mail por concepto de cambio de clave: $clave.\nInfo del error: {$mailPrili->ErrorInfo}");
				return "No se pudo enviar la clave por email.";
			}
			return "";
		}
		else {
			return "Esa dirección de email no pretenece a ninguna de las cuentas guardadas previamente.";
		}
	}
	
	function cambiarClaveComprador($email, $actual, $nueva, $confirmacion){
		$idComprador = (int)$this->autenticarComprador($email, $actual);
		if($idComprador > 0){
			$security = new Seguridad($this->Cnx);
			$objComprador = new Comprador($this->Cnx);
			$objComprador->setPassword($idComprador, $security->Encriptar($nueva));
			return "";
		}
		else {
			return "Contraseña actual incorrecta.";
		}
	}
	
	/* METODOS REFERENTES AL MEDIO DE PAGO ABITAB */
	
	function obtenerString25($idVenta){
		// Para la primera barra de codigo
		$codigoEmpresa = COD_CLIENTE_ABITAB;
		$codDocumento = '';
		$vencimiento = date('dmY'); // No aplica, se pone la fecha de hoy
		
		// Hay que obtener el codigo de cliente/invitado y armar el string que es de largo 7
		$venta = new Venta($this->Cnx);
		$codCliente = str_pad($venta->obtenerIdCliente($idVenta), 7, "0", STR_PAD_LEFT);
		
		// Hay que armar el string de documento que es de largo 7
		$codDocumento = str_pad($idVenta, 7, "0", STR_PAD_LEFT);
		
		return $codigoEmpresa . $codCliente . $codDocumento . $vencimiento;
	}
	
	function obtenerString24($idVenta, $codigo25){
		// Para la segunda barra de codigo
		$venta = new Venta($this->Cnx);
		$resCab = iterator_to_array($venta->obtenerDatosCabezal($idVenta));
		$cabezal = $resCab[0];
		
		// Importe con dos digitos decimales, largo 11
		$importe = str_pad(str_replace(".", "", $cabezal['total']), 11, "0",  STR_PAD_LEFT);
		
		// 1 si es pesos, 2 si es dolares o lo que sea
		$moneda = $cabezal['moneda'] == MONEDA_BASE ? "1" : "2";
		$cuota = "00"; // No aplica
		$mora = "0"; // No aplica
		$tipoDocumento = "1"; // Siempre factura
		$nroCuenta = "0000000"; // No aplica
		
		$codigo23 = $importe . $moneda . $cuota . $mora . $tipoDocumento . $nroCuenta;
		$digitoControl = $this->generarDigitoControl($codigo23, $codigo25); // Algoritmo
		
		return $codigo23 . $digitoControl;
	}
	
	// Rutina para generar el digito de control
	function generarDigitoControl($codigo23, $codigo25){
		/* 
		*  Paso (1): Corresponder cada posición numérica de la string total de la factura a 
		*  utilizar para el código de barras, con el vector de ponderacion que se define debajo.
		*/
		
		$strPonderacion = "634456327329876344563273298763445632732987634";
		$strBarra = substr($codigo25 . $codigo23, 3); // Todos los digitos menos los de codigo de empresa
		$vectorPonderacion = str_split($strPonderacion, 1);
		$vectorBarra = str_split($strBarra, 1);
		
		// Test: QUITAR
		if(count($vectorPonderacion) != count($vectorBarra)){
			die("Mal armados los arrays");
		}
		
		/* 
		*  Paso (2): Multiplicar valor de cada posición de la factura por valor de la misma posición correspondiente
		*  del vector de ponderacion, de acuerdo al punto 1.
		*  Calcular el Módulo 10 del resultado de cada multiplicación.
		*/
		// Comienzo a multiplicar los valores de cada posicion y les aplico el MOD 10
		$arrResultados = array();
		for($i=0; $i < 45; $i++){
			$arrResultados[$i] = ($vectorBarra[$i] * $vectorPonderacion[$i]) % 10;
		}
		
		/*  
		*  Paso (3): Sumar todos estos resultados (restos) del paso anterior entre sí.
		*/
		$suma = array_sum($arrResultados);
		
		/*  
		*  Paso (4): Calcular el Módulo 10 del resultado obtenido en el paso 3.
		*/
		$modulo = $suma % 10;
		
		/*  
		*  Paso (5): Restarle a 10 el resultado del paso anterior (complemento a 10
		*  al resultado del paso 4 => 10 – resultado paso 4.
		*/
		$complemento = 10 - $modulo;
		
		/*  
		*  Paso (6): Si el resultado obtenido en el paso anterior no es 10, 
		*  dicho valor será el dígito de control, de lo contrario el mismo será 0. 
		*  Esto es equivalente a calcular nuevamente el Módulo 10 a dicho resultado (resto de 
		*  dividir el valor obtenido en el punto 5 entre 10).
		*/
		$digito = ($complemento == 10) ? 0 : $complemento;
		
		return $digito;
	}
	
	/* FIN ABITAB */
	
	function procesarContactoSitio($datosContacto){
		$mailPrili = new PHPMailer();
		$mailPrili->IsHTML(true);
		$mailPrili->IsMail();
		$mailPrili->Host = MAIL_HOST;
		$mailPrili->From = $datosContacto['email'];
		$mailPrili->FromName = $datosContacto['nombre'];	
		$mailPrili->Subject = "Nueva consulta desde el sitio";
		// Destinatario 
		$mailPrili->AddAddress(CASILLA_NOTIFICACION_CONTACTO);
		$mailPrili->AddBCC("mcaravia@narthex.com.uy");
		$emailBody = '<br /><img height="53px" src="' . DIR_HTTP_PUBLICA . 'images/logo.png"><br /><br /><br />';
		$emailBody .= "Nombre:{$datosContacto['nombre']}<br />";
		$emailBody .= "Direccion:{$datosContacto['direccion']}<br />";
		$emailBody .= "Telefono:{$datosContacto['telefono']}<br />";
		$emailBody .= "Email:{$datosContacto['email']}<br />";
		$emailBody .= "Consulta:<br />{$datosContacto['consulta']}";
		$mailPrili->Body = $mailPrili->WrapText($emailBody, 72);
		$success = $mailPrili->Send();
		if($success === FALSE){
			LogArchivo("No se pudo enviar mail por concepto de consulta desde el sitio.\nInfo del error: {$mailPrili->ErrorInfo}");
			return "No se pudo enviar la consulta.";
		}
		return "";
	}
	
	// Devuelve el monto minimo de venta aceptado segun la configuracion
	function obtenerMontoMinimoVenta(){
		$monto = $this->Cnx->getOne("SELECT monto_minimo_compra FROM configuracion");
		return $monto;
	}

	function decrementarStock($idPrenda, $idColor, $idTalle, $cantidad){
		$sqlUpdate = "UPDATE prenda_stock ";
		$sqlUpdate .= "SET cantidad = (cantidad - {$cantidad}) ";
		$sqlUpdate .= "WHERE id_prenda = {$idPrenda} AND id_talle = {$idTalle} AND id_color = {$idColor}";
		$ok = $this->Cnx->execute($sqlUpdate);
		if($ok === false){
			LogArchivo("Fallo la siguiente consulta tratando de actualizar stock:\n$sqlUpdate");
			return "Fallo la consulta tratando de actualizar stock";
		}
		return '';
	}

	function obtenerOpcionesPagoOca(){
		return array(1, 2, 3);
	}

	//Verifica la existencia del Cupon, asi como si ya se uso y si esta en fecha valida
	function verificarCodCupon($codCupon){
		//verifico si el cupon existe...
		$sqlSelect = "select codigo, utilizado, fechaini, fechafin, valor from cupon_codigo cc inner join cupon c on cc.id_cupon = c.id_cupon where codigo = '$codCupon'";
		$res = $this->Cnx->execute($sqlSelect);
		if($res->EOF){
			LogArchivo("El código $codCupon es incorrecto!");
			return "El código $codCupon es incorrecto!";
		}
		else{
			//while(!$res->EOF){
			//si trae algo, deberia ser 1 solo registro...
				$auxUtilizado = $res->fields['utilizado'];
				if($auxUtilizado == 1){
					LogArchivo("El cupon $codCupon ya fue utilizado!");
					return "El cupon $codCupon ya fue utilizado!";
				}
				$auxFechaIni =  $res->fields['fechaini'];
				$auxFechaFin =  $res->fields['fechafin'];
				$auxFechaActual = date("Y-m-d H:i:s");
				//LogArchivo("Fecha Ini: $auxFechaIni, Fecha Fin: $auxFechaFin, Fecha Actual: $auxFechaActual");
				if(!($auxFechaIni <= $auxFechaActual && $auxFechaActual <= $auxFechaFin)){
					LogArchivo("El cupon $codCupon está vencido! el período era entre $auxFechaIni y $auxFechaFin.");
					return "El cupon $codCupon está vencido! el período era entre $auxFechaIni y $auxFechaFin.";	
				}
				$auxValor = $res->fields['valor'];
				LogArchivo("Interfaz.class.php::verificarCodCupon -> retorna: $auxValor");
				return $auxValor;
			// 	$res->moveNext();
			// }
		}

		// $cntProds = $this->DB->getOne("SELECT COUNT(*) FROM prenda_comprador WHERE id_comprador = $id");
		// if($cntProds > 0){
		// 	$this->Error .= "El comprador tiene $cntProds ventas asociadas. ";
		// }

		// $rows = iterator_to_array($res);
		// foreach($rows as $row){
		// $valorCol_xxx = $row['xxx'];
		// }

	}
}
?>