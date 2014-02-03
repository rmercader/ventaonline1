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

$datosComprador = $_SESSION["DATOS_VENTA"]['comprador'];
$datosVenta = $_SESSION["DATOS_VENTA"]['venta'];
$infoExtra = $_SESSION["DATOS_VENTA"]['extra'];

$interfaz = new Interfaz();

if($_SERVER['REQUEST_METHOD'] == "POST"){
	$Idtrn = trim($_POST["Idtrn"]);
	$Info = $_POST["Info"];
	$Rsp = (int) $_POST["Rsp"];

	// Logueo
	LogArchivo("Retorno desde los servidores de OCA.");
	LogArchivo("Parametros recibidos: Idtrn = $Idtrn, Info = $Info, Rsp = $Rsp");

	// Si hay transaccion y el cliente acepto la compra
	if($Idtrn != "" && $Rsp == 0){

		// Hay que consultar si la transaccion fue autorizada por el sistema de OCA
		$postFields = array(
			'Idtrn' => $Idtrn, 
			'Nrocom' => NRO_COM_OCA, 
			'Nroterm' => NRO_TERM_OCA, 
			'Moneda' => MONEDA_OCA, 
			'Importe' => str_replace('.', '', $datosVenta['total']), 
			'Plan' => $infoExtra["cuotas_oca"], 
			'Info' => "test"
		);
		
		//$serverAddr = "190.64.15.113";
		$serverAddr = "compraswebcomercio.oca.com.uy";

		// Logueo
		LogArchivo("El cliente confirmo la compra, procedemos a consultar si fue Autorizada o Denegada...");
		LogArchivo("Contactando URL: https://$serverAddr/presentacion.aspx");
		LogArchivo("Datos enviados (JSON): " . json_encode($postFields));

		// Solicitud POST a https://compraswebcomercio.oca.com.uy/presentacion.aspx
		$ch = curl_init("https://$serverAddr/presentacion.aspx"); // SANDBOX
		//$ch = curl_init("https://compraswebcomercio.oca.com.uy/presentacion.aspx");
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSLCERT, "/home/prili/ssl/certs/prili.net.crt");
		curl_setopt($ch, CURLOPT_SSLKEY, "/home/prili/ssl/private/prili.net.key");
		$resultXml = curl_exec($ch);

		if(!$resultXml){
			$errorOca = curl_error($ch);
			LogArchivo("Ocurrio el siguiente error al contactar la URL https://$serverAddr/presentacion.aspx del servicio de OCA: $errorOca");
			$errorAlUsuario = "Lamentablemente no pudimos establecer una conexión con los servidores de Oca.\n";
			$errorAlUsuario .= "Es posible que los mismos se encuentren temporalmente sobrecargados, por lo tanto \n";
			$errorAlUsuario .= "te recomendamos que vuelvas a intentar en unos instantes.";
			$_SESSION["ERRORES_VENTA"] = $errorAlUsuario;
			header("Location: checkout-local.php");
			exit();
		} else {
			
			// Logueo
			LogArchivo("Respuesta dada por el servidor: ");
			LogArchivo($resultXml);

			$xml = simplexml_load_string($resultXml, NULL, LIBXML_NOCDATA);
			$Rsp = (string) $xml->Rsp;
			$Nrorsv = (string) $xml->Nrorsv;
			$Info = $xml->Info;

			if($Rsp == "0"){
				// Transaccion autorizada
				$infoExtra["nrorsv"] = $Nrorsv;
				$res = $interfaz->registrarVenta($datosComprador, $datosVenta, $infoExtra);
				if($res != "" && !is_numeric($res)){
					LogArchivo("Error salvando venta con OCA: $res | Info: $Info | Rsp: $Rsp");
					$_SESSION["ERRORES_VENTA"] = $res;
					header("Location: checkout-local.php");
					exit();
				}
				else if(is_numeric($res)){
					unset($_SESSION["DATOS_VENTA"]);
					$_SESSION["ULTIMA_VENTA"] = $res;
					header("Location: compra-confirmada.php");
					exit();
				}
			} else {
				// Transaccion no autorizada
				LogArchivo("Transaccion $Idtrn por venta con OCA no autorizada: Info: $Info | Rsp: $Rsp");
				$_SESSION["ERRORES_VENTA"] = "La transaccion no fue autorizada por el servidor de pagos de OCA Card.";
				header("Location: checkout-local.php");
				exit();
			}
		}
	} else {
		LogArchivo("Accion del cliente frente a una compra OCA: $Rsp");
		$_SESSION["ERRORES_VENTA"] = "";
		header("Location: checkout-local.php");
		exit();
	}
}

?>