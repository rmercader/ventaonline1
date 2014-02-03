<?PHP
// Evito CACHE
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Inicio Session
session_start();
//ini_set('display_errors', 1);
include_once('app.config.php');
include_once('sitio.config.php');
include_once(DIR_BASE.'funciones-auxiliares.php');
include(DIR_LIB.'nyiLIB.php');
include(DIR_LIB.'nyiHTML.php');
include(DIR_LIB.'nyiDATA.php');
include_once(DIR_BASE.'seguridad/usuario.class.php');
include_once(DIR_BASE.'class/interfaz.class.php'); 
include_once('funciones-sitio.php');
require_once(DIR_XAJAX.'xajax_core/xajax.inc.php');

$interfaz = new Interfaz();
$seccion = new nyiHTML('checkout-local.htm');

// Obtenemos los departamentos
$idsDepartamentos = $interfaz->obtenerIdsDepartamentos();
$dscDepartamentos = $interfaz->obtenerDscDepartamentos();
$seccion->assign('departamento_id', $idsDepartamentos);
$seccion->assign('departamento_dsc', $dscDepartamentos);
$pagosOca = $interfaz->obtenerOpcionesPagoOca();
$seccion->assign('cuotas_oca_vals', $pagosOca);
$xajax = new xajax();
$xajax->setRequestURI('ventas-ajaxhelper.php');
$xajax->registerFunction('llenarCarritoLocal');
$xajax->registerFunction("calcularEnvio");
$xajax->registerFunction("verificarCuenta");
$xajax->registerFunction("autenticarYObtenerComprador");
$xajax->registerFunction("verificarCodCupon");
$xajax->processRequest();
$seccion->assign('AJAX_JAVASCRIPT', $xajax->getJavascript(DIR_XAJAX));

$nombre = isset($_POST["nombre"]) ? trim($_POST["nombre"]) : '';
$apellido = isset($_POST["apellido"]) ? trim($_POST["apellido"]) : '';
$email = isset($_POST["email"]) ? trim($_POST["email"]) : '';
$emailConf = isset($_POST["emailconf"]) ? trim($_POST["emailconf"]) : '';
$direccion = isset($_POST["direccion"]) ? trim($_POST["direccion"]) : '';
$idDepartamento = isset($_POST["id_departamento"]) ? trim($_POST["id_departamento"]) : 0;
$ciudad = isset($_POST["ciudad"]) ? trim($_POST["ciudad"]) : '';
$codPostal = isset($_POST["codigo_postal"]) ? trim($_POST["codigo_postal"]) : '';
$telefono = isset($_POST["telefono"]) ? trim($_POST["telefono"]) : '';
$esInvitado = isset($_POST["is_anonymous"]) ? trim($_POST["is_anonymous"]) : '';
$medioPago = isset($_POST["medio_pago"]) ? trim($_POST["medio_pago"]) : '';
$costoEnvio = isset($_POST["envio"]) ? $_POST["envio"] : '';
$total = isset($_POST["total"]) ? str_replace(',', '', $_POST["total"]) : '';
$descuento = isset($_POST["descuento"]) ? str_replace(',', '', $_POST["descuento"]) : '';
$clave = isset($_POST["clave"]) ? $_POST["clave"] : '';
$claveConf = isset($_POST["clave_conf"]) ? $_POST["clave_conf"] : '';
$idComprador = isset($_POST["id_comprador"]) ? $_POST["id_comprador"] : '';
$cuotasOca = isset($_POST["cuotas_oca"]) ? (int) $_POST["cuotas_oca"]: 0;
$cupon = isset($_POST["cupon"]) ? trim($_POST["cupon"]) : '';

$errores = "";
if(isset($_SESSION["ERRORES_VENTA"])){
	$errores = $_SESSION["ERRORES_VENTA"];
	unset($_SESSION["ERRORES_VENTA"]);
}
$exitos = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
	//$errores = "estoy confirmando la compra";
	if($email == ''){
		$errores .= "Por favor ingrese su email.\n";
	}
	elseif(intval($idComprador) == 0 && $email != $emailConf){
		$errores .= "El email y su confirmación no coinciden.\n";
	}
	if($nombre == ''){
		$errores .= "Por favor ingrese su nombre.\n";
	}
	if($apellido == ''){
		$errores .= "Por favor ingrese su apellido.\n";
	}
	if($direccion == ''){
		$errores .= "Por favor ingrese su dirección.\n";
	}
	if($idDepartamento == 0){
		$errores .= "Por favor ingrese su departamento.\n";
	}
	if($ciudad == ''){
		$errores .= "Por favor ingrese su ciudad.\n";
	}
	if($codPostal == ''){
		$errores .= "Por favor ingrese su código postal.\n";
	}
	if($telefono == ''){
		$errores .= "Por favor ingrese su teléfono.\n";
	}
	if($esInvitado == 0){
		if($clave == ""){
			$errores .= "Por favor ingrese su contraseña.\n";
		}
		else if(intval($idComprador) == 0 && $claveConf != $clave){
			$errores += "La contraseña y su confirmación no coinciden.\n";
		}
	}
	
	if($errores == ""){
		// Procedo a cargar la informacion de la compra
		$datosComprador = array(
			"id_comprador"=>$idComprador,
			"email"=>$email,
			"nombre"=>$nombre,
			"apellido"=>$apellido,
			"direccion"=>$direccion,
			"id_departamento"=>$idDepartamento,
			"codigo_postal"=>$codPostal,
			"telefono"=>$telefono,
			"ciudad"=>$ciudad,
			"clave"=>$clave
		);
		
		// Tengo que ver si es compra o si actualizacion de datos
		if(is_array($_SESSION["CARRITO"]["ITEMS"]) && count(array_values($_SESSION["CARRITO"]["ITEMS"])) > 0){
			$datosVenta = array(
				"items"=>array_values($_SESSION["CARRITO"]["ITEMS"]),
				"costo_envio"=>$costoEnvio,
				"descuento"=>$descuento,
				"total"=>$total
			);
			$infoExtra = array(
				"invitado"=>$esInvitado,
				"medio_pago"=>$medioPago,
				"codigo_Cupon"=>$cupon,
			);
			
			switch ($medioPago) {
				case MEDIO_PAGO_ABITAB:
					$res = $interfaz->registrarVenta($datosComprador, $datosVenta, $infoExtra);
					if($res != "" && !is_numeric($res)){
						$errores = $res;
					}
					else if(is_numeric($res)){
						$_SESSION["ULTIMA_VENTA"] = $res;
						header("Location: compra-confirmada.php");
						exit();
					}
					break;
				
				case MEDIO_PAGO_OCA:
					$infoExtra["cuotas_oca"] = $cuotasOca;
					$datosVentaJson = json_encode(array('comprador'=>$datosComprador, 'venta'=>$datosVenta, 'extra'=>$infoExtra));
					$totalOca = str_replace('.', '', $total);
					
					$postFields = array(
						'Nrocom' => NRO_COM_OCA, 
						'Nroterm' => NRO_TERM_OCA, 
						'Moneda' => MONEDA_OCA, 
						'Importe' => $totalOca, 
						'Plan' => $cuotasOca, 
						'TCompra' => TIPO_COMPRA_OCA,
						'Urlresponse' => '', 
						'Info' => "test", 
						'Tconn' => "0"
						/*'Info' => $datosVentaJson */
					);
					// Solicitud POST a https://compraswebcomercio.oca.com.uy/inicio.aspx
					//$server = "190.64.15.113"; // SANDBOX
					$server = "compraswebcomercio.oca.com.uy";

					// Logueo
					LogArchivo("Iniciando proceso de pago con servidores de OCA...");
					LogArchivo("Contactando URL: https://$server/inicio.aspx");
					LogArchivo("Datos enviados (JSON): " . json_encode($postFields));

					$ch = curl_init("https://$server/inicio.aspx");
					curl_setopt($ch, CURLOPT_HEADER, FALSE);
					curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
					curl_setopt($ch, CURLOPT_VERBOSE, true);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_SSLVERSION, 3);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($ch, CURLOPT_SSLCERT, "/home/prili/certs/prili.net.crt");
					curl_setopt($ch, CURLOPT_SSLKEY, "/home/prili/certs/prili.net.key");
					$resultXml = curl_exec($ch);
					if(!$resultXml){
						$errorOca = curl_error($ch);
						// Logueo
						LogArchivo("Ocurrio el siguiente error al contactar la URL https://$server/inicio.aspx del servicio de OCA: $errorOca");
						$errores = "Lamentablemente no pudimos establecer una conexión con los servidores de Oca.\n";
						$errores .= "Es posible que los mismos se encuentren temporalmente sobrecargados, por lo tanto \n";
						$errores .= "te recomendamos que vuelvas a intentar en unos instantes.";
					} else {
						// Logueo
						LogArchivo("Datos recibidos: ");
						LogArchivo($resultXml);

						$xml = simplexml_load_string($resultXml, NULL, LIBXML_NOCDATA);
						$Nrocom = (string) $xml->Nrocom;
						$Nroterm = (string) $xml->Nroterm;
						$Moneda = (string) $xml->Moneda;
						$Importe = (int) $xml->Importe;
						$Plan = (int) $xml->Plan;
						$TCompra = (int) $xml->TCompra;
						$Info = $xml->Info;
						$Idtrn = trim($xml->Idtrn);

						$errXml = "";
						if($Nrocom != NRO_COM_OCA){
							$errXml .= "Nrocom\n";
						}
						if($Nroterm != NRO_TERM_OCA){
							$errXml .= "Nroterm\n";
						}
						if($Moneda != MONEDA_OCA){
							$errXml .= "Moneda\n";
						}
						if($Importe != $totalOca){
							$errXml .= "Importe $Importe\n";
						}
						if($Plan != $cuotasOca){
							$errXml .= "Plan\n";
						}
						if($TCompra != TIPO_COMPRA_OCA){
							$errXml .= "TCompra\n";
						}
						/*if($Info != $datosVentaJson){
							$errXml .= "Info\n";
						}*/
						if($TCompra != TIPO_COMPRA_OCA){
							$errXml .= "TCompra\n";
						}
						if($Idtrn == ""){
							$errXml .= "Idtrn vacio\n";
						}

						if($errXml != ""){
							LogArchivo("Mala respuesta del primer llamado: \n$errXml");
						}
						else {
							// Redireccion
							$UrlRetorno = urlencode("http://www.prili.net/procesar-venta-oca.php");
							
							$_SESSION["DATOS_VENTA"]["comprador"] = $datosComprador; // Por si las moscas
							$_SESSION["DATOS_VENTA"]["venta"] = $datosVenta; // Por si las moscas
							$_SESSION["DATOS_VENTA"]["extra"] = $infoExtra; // Por si las moscas

							$serverAddr = "comprasweb.oca.com.uy";
							//$serverAddr = "190.64.15.112"; // Sandbox

							// Logueo
							LogArchivo("Redirigiendo usuario a los servidores de OCA: https://$serverAddr/index.aspx?Idtrn=$Idtrn&Urlresponse=$UrlRetorno");

							header("Location: https://$serverAddr/index.aspx?Idtrn=$Idtrn&Urlresponse=$UrlRetorno");
							exit();
						}
					}
					
					break;
			}
			
		}
		else if($esInvitado == 0){
			// Actualizacion de datos de cuenta
			$res = $interfaz->actualizarDatosComprador($datosComprador);
			if($res != ""){
				$errores = $res;
			}
			else {
				$_SESSION["mensaje-exito"] = "Sus datos fueron actualizados correctamente.";
				$exitos = "Sus datos fueron actualizados correctamente.";
				$nombre = "";
				$apellido = "";
				$email = "";
				$emailConf = "";
				$direccion = "";
				$idDepartamento = 0;
				$ciudad = "";
				$codPostal = "";
				$telefono = "";
				$esInvitado = "";
				$clave = "";
				$claveConf = "";
				$idComprador = "";
			}
		}
	}
}

$seccion->assign('nombre', $nombre);
$seccion->assign('apellido', $apellido);
$seccion->assign('email', $email);
$seccion->assign('emailconf', $emailConf);
$seccion->assign('direccion', $direccion);
$seccion->assign('id_departamento', $idDepartamento);
$seccion->assign('ciudad', $ciudad);
$seccion->assign('codigo_postal', $codPostal);
$seccion->assign('telefono', $telefono);
$seccion->assign('clave', $clave);
if($errores != "")
	$seccion->assign('errores', nl2br($errores));
if($exitos != "")
	$seccion->assign('exitos', $exitos);
$seccion->assign('envio', $costoEnvio);

$seccion->printHTML();

?>