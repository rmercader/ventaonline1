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

function verificarCuenta($email, $esInvitado){
	$objResponse = new xajaxResponse();
	$objResponse->assign("clave", "value", "");
	
	if(!esEmailValido($email)){
		$objResponse->assign("errEmailInvalido", "style.display", "block");
		$objResponse->assign("checkout_msg", "innerHTML", "Por favor ingrese su email");
	}
	else{
		$objResponse->assign("checkout_msg", "style.display", "block");
		$interfaz = new Interfaz();
		$idComprador = $interfaz->idCompradorPorEmail($email);
		if(intval($idComprador) > 0){
			// El comprador esta registrado
			$objResponse->assign("checkout_msg", "innerHTML", "Su cuenta fue encontrada por una transacción anterior. Ingrese su contraseña abajo para recuperar su información previamente guardada y poder actualizar la misma.");
			$objResponse->assign("divClave", "style.display", "block");
			$objResponse->assign("btn-continuar", "style.display", "block");
			$objResponse->assign("divClave", "style.display", "block");
			$objResponse->assign("olvidoClave", "style.display", "block");
			$objResponse->assign("cambioClave", "style.display", "block");
			$objResponse->call("focusClave");
		}
		else{
			$objResponse->assign("checkout_msg", "innerHTML", "Esa dirección de email no pretenece a ninguna de las cuentas guardadas previamente. Si desea crear una cuenta deberá hacerlo al momento de confirmar una compra.");
			$objResponse->assign("divClave", "style.display", "none");
			$objResponse->assign("btn-continuar", "style.display", "none");
			$objResponse->assign("btn-confirm", "style.display", "none");
			$objResponse->assign("divDatos", "style.display", "none");
			$objResponse->assign("olvidoClave", "style.display", "none");
			$objResponse->assign("cambioClave", "style.display", "none");
			$objResponse->assign("id_comprador", "value", 0);
		}
		$objResponse->assign("errEmailInvalido", "style.display", "none");
	}
	$objResponse->assign("login_ajax", "style.display", "none");
	return $objResponse;
}

function enviarNuevaClave($mail){
	$objResponse = new xajaxResponse();
	$interfaz = new Interfaz();
	if(esEmailValido($mail)){
		$res = $interfaz->enviarNuevaClave($mail);
		if($res == ""){
			$res = "Su nueva contraseña fue enviada correctamente a su dirección de email.";
		}
		$objResponse->assign("checkout_msg", "innerHTML", $res);
	}
	else{
		$objResponse->assign("errEmailInvalido", "style.display", "block");
		$objResponse->assign("checkout_msg", "innerHTML", "Por favor ingrese su email");
	}
	$objResponse->assign("login_ajax", "style.display", "none");
	return $objResponse;
}

function cambiarClave($email, $actual, $nueva, $confirmacion){
	$objResponse = new xajaxResponse();
	$interfaz = new Interfaz();
	if($nueva == $confirmacion){
		$res = $interfaz->cambiarClaveComprador($email, $actual, $nueva, $confirmacion);
		if($res == ""){
			$objResponse->call("cambiarClaveExito");
		}
		else {
			$objResponse->call("cambiarClaveError", $res);
		}
	}
	else{
		$objResponse->call("cambiarClaveError", "La contraseña nueva y su confirmación no coinciden.");
	}
	return $objResponse;
}

function autenticarYObtenerComprador($mail, $clave){
	$objResponse = new xajaxResponse();
	$interfaz = new Interfaz();
	$idComprador = $interfaz->autenticarComprador($mail, $clave);
	if(intval($idComprador) > 0){
		$objResponse->assign("checkout_msg", "innerHTML", "La información de su cuenta ha sido recuperada satisfactoriamente.");
		// Procedo a cargar los datos del comprador
		$datosComprador = $interfaz->datosCompradorPorId($idComprador);
		$objResponse->assign("id_comprador", "value", $idComprador);
		$objResponse->assign("nombre", "value", $datosComprador["nombre"]);
		$objResponse->assign("apellido", "value", $datosComprador["apellido"]);
		$objResponse->assign("direccion", "value", $datosComprador["direccion"]);
		$objResponse->assign("id_departamento", "value", $datosComprador["id_departamento"]);
		$objResponse->assign("ciudad", "value", $datosComprador["ciudad"]);
		$objResponse->assign("codigo_postal", "value", $datosComprador["codigo_postal"]);
		$objResponse->assign("telefono", "value", $datosComprador["telefono"]);
		$objResponse->assign("btn-confirm", "style.display", "block");
		$objResponse->assign("divDatos", "style.display", "block");
	}
	else{
		$objResponse->assign("checkout_msg", "innerHTML", '<font style="color: #D03B39; font-weight: bold;">Contraseña Incorrecta:</font><br>Esta dirección electrónica parece correcta, pero la contraseña no lo es. Por favor re-ingrese su contraseña , o haga clic en el enlace abajo para obtener una contraseña temporal que será enviada a su correo electrónico.');
		$objResponse->assign("btn-confirm", "style.display", "none");
		$objResponse->assign("divDatos", "style.display", "none");
	}
	$objResponse->assign("login_ajax", "style.display", "none");
	return $objResponse;
}

// Ajax
$xajax = new xajax();
$xajax->registerFunction("enviarNuevaClave");
$xajax->registerFunction("verificarCuenta");
$xajax->registerFunction("autenticarYObtenerComprador");
$xajax->registerFunction("cambiarClave");
$xajax->processRequest();
$xajax->printJavascript(DIR_XAJAX);

?>