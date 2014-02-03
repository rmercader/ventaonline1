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

// @param $items es el array JSONP que te manda el foxycart
function llenarCarritoLocal($items){
	$objResponse = new xajaxResponse();
	$interfaz = new Interfaz();
	
	if(count($items) > 0){
		$objResponse->assign('liInvitado', 'style.display', 'block');
		$objResponse->assign('divDetalles', 'style.display', 'block');
		$objResponse->assign('divSubtotales', 'style.display', 'block');
		$objResponse->assign('divMediosPago', 'style.display', 'block');
		// $objResponse->assign('divCuenta', 'style.width', '36%');
		// $objResponse->assign('divDatos', 'style.width', '37%');
	}
	
	// Si hay carrito levantado
	if(isset($_SESSION["CARRITO"])){
		unset($_SESSION["CARRITO"]);
	}
	$_SESSION["CARRITO"] = array();
	$_SESSION["CARRITO"]["ITEMS"] = array();
	
	$subtotal = 0;
	$detalles = array();
	// Asi se obtienen los items
	foreach($items as $prenda){
		$idPrenda = $prenda["code"];
		$nombre = $prenda["name"];
		$expColor = explode(" - ", $prenda["options"]["color"]);
		$idColor = $expColor[0]; // $idColor - $nombreColor
		$idTalle = $interfaz->obtenerIdTallePorCodigo($prenda["options"]["talle"]); // Codigo talle
		$precio = $interfaz->obtenerPrecioPrenda($idPrenda);
		$cantidad = $prenda["quantity"];
		$subtotal += $precio * $cantidad;
		
		// Lo insertamos
		$dsc = "$nombre, color {$expColor[0]}, talle {$prenda['options']['talle']}";
		$itemCompra = array(
			"id_prenda"=>$idPrenda, 
			"id_talle"=>$idTalle, 
			"id_color"=>$idColor, 
			"cantidad"=>$cantidad, 
			"descripcion"=>$dsc, 
			"precio"=>$precio, 
			"subtotal"=>$precio * $cantidad
		);
		$_SESSION["CARRITO"]["ITEMS"]["$idPrenda-$idTalle-$idColor"] = $itemCompra;
		
		array_push($detalles, array(
			"nombre"=>$nombre, 
			"color"=>$expColor[1], 
			"talle"=>$prenda["options"]["talle"], 
			"cantidad"=>$cantidad,
			"total"=>number_format(round($precio * $cantidad, 2), 2),
			"unitario"=>number_format(round($precio, 2), 2)
		));
	}
	
	$subtotalVista = number_format(round($subtotal, 2), 2);
	$tablaDetalles = new nyiHTML("tabla-detalles-checkout.htm");
	$tablaDetalles->assign("subtotal", $subtotal);
	$tablaDetalles->assign("total", $subtotal);
	$tablaDetalles->assign("items", $detalles);
	
	$objResponse->assign('btn-confirm', 'style.display', 'block');
	
	// Temas de minimo monto de compra
	$minimo = $interfaz->obtenerMontoMinimoVenta();
	$msgMinimo = "";
	// Si hay una compra en proceso y no se llego al minimo
	if($subtotal > 0 && $subtotal < $minimo){
		$objResponse->assign('btn-confirm', 'style.display', 'none');
		$msgMinimo = '<br /><font style="font-weight: bold; color: #D03B39;">Para poder completar su compra, la misma deberá ser por un importe mínimo de $' . $minimo . '.</font>';
	}
	
	$objResponse->assign('divDetalles', 'innerHTML', $tablaDetalles->fetchHTML() . $msgMinimo);
	
	$objResponse->assign('subtotal', 'value', $subtotalVista);
	$objResponse->assign('total', 'value', $subtotalVista);
	$objResponse->call('habilitarDepartamento');
	$objResponse->call('calcularCostoEnvio');
	
	return $objResponse;
}

function calcularEnvio($idDepartamento, $subtotal){
	$objResponse = new xajaxResponse();
	$interfaz = new Interfaz();
	$costoenvio = 0;
	if(is_numeric($idDepartamento) && $idDepartamento > 0){
		$costoenvio = $interfaz->obtenerCostoEnvio($idDepartamento);
	}
	
	$numSubtotal = str_replace(",", "", $subtotal);
	$objResponse->assign('envio', 'value', number_format($costoenvio, 2));
	$objResponse->assign('total', 'value', number_format($numSubtotal+$costoenvio, 2));
	$objResponse->assign('divEnvio', 'innerHTML', number_format($costoenvio, 2));
	$objResponse->assign('divTotal', 'innerHTML', number_format($numSubtotal+$costoenvio, 2));
	return $objResponse;
}

function verificarCuenta($email, $esInvitado){
	$objResponse = new xajaxResponse();
	$objResponse->assign("clave", "value", "");
	$objResponse->assign("clave_conf", "value", "");
	if(!esEmailValido($email)){
		$objResponse->assign("errEmailInvalido", "style.display", "block");
		$objResponse->assign("checkout_msg", "innerHTML", "Por favor ingrese su email");
	}
	else{
		if(!$esInvitado){
			$objResponse->assign("checkout_msg", "style.display", "block");
			$interfaz = new Interfaz();
			$idComprador = $interfaz->idCompradorPorEmail($email);
			if(intval($idComprador) > 0){
				// El comprador esta registrado
				$objResponse->assign("checkout_msg", "innerHTML", "Su cuenta fue encontrada por una transacción anterior. Usted puede ingresar su contraseña abajo para recuperar su información previamente guardada, o usted puede comprar sin registrarse.");
				$objResponse->assign("divClave", "style.display", "block");
				$objResponse->assign("divClaveConf", "style.display", "none");
				$objResponse->assign("btn-continuar", "style.display", "block");
				$objResponse->assign("id_comprador", "value", $idComprador);
				$objResponse->assign("is_anonymous_0", "checked", "true");
				if(isset($_SESSION["CARRITO"]) && count($_SESSION["CARRITO"]["ITEMS"]) > 0){
					$objResponse->assign("spnCrearUsarCuenta", "innerHTML", "Usar mi cuenta anteriormente guardada");
				}
				else {
					$objResponse->call("focusClave");
				}
				$objResponse->assign("liMailConf", "style.display", "none");
			}
			else{
				$objResponse->assign("checkout_msg", "innerHTML", "Esa dirección de email no pretenece a ninguna de las cuentas guardadas previamente. Por favor seleccione \"Crear una Cuenta\" si usted desea guardar la información para su próxima visita.");
				$objResponse->assign("divClave", "style.display", "block");
				$objResponse->assign("divClaveConf", "style.display", "block");
				$objResponse->assign("spnCrearUsarCuenta", "innerHTML", "Crear una Cuenta");
				$objResponse->assign("liMailConf", "style.display", "block");
				$objResponse->assign("id_comprador", "value", 0);
			}
		}
		else {
			$objResponse->assign("checkout_msg", "style.display", "none");
		}
		$objResponse->assign("errEmailInvalido", "style.display", "none");
	}
	$objResponse->assign("login_ajax", "style.display", "none");
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
		$objResponse->call('calcularCostoEnvio');
	}
	else{
		$objResponse->assign("checkout_msg", "innerHTML", '<font style="color: #D03B39; font-weight: bold;">Contraseña Incorrecta:</font><br>Esta dirección electrónica parece correcta, pero la contraseña no lo es. Por favor re-ingrese su contraseña , o haga clic en el enlace abajo para obtener una contraseña temporal que será enviada a su correo electrónico.');
	}
	$objResponse->assign("login_ajax", "style.display", "none");
	return $objResponse;
}

function verificarCodCupon($codCupon, $auxSubTotal, $auxEnvio){
	$objResponse = new xajaxResponse();
	$interfaz = new Interfaz();
	$auxVerificar = $interfaz->verificarCodCupon($codCupon);
	$auxDescuentoNumerico = 0;
	if(is_numeric($auxVerificar)){
		$auxDescuentoNumerico = $auxVerificar;
		$objResponse->assign("errCupon", "value", "");
		$objResponse->assign("errCupon", "style.display", "none");
		$objResponse->assign("aceptCupon", "style.display", "block");
	}
	else{
		$objResponse->assign("errCupon", "value", $auxVerificar);
		$objResponse->assign("errCupon", "style.display", "block");
		$objResponse->assign("aceptCupon", "style.display", "none");
	}
	$numSubTotal = str_replace(",", "", $auxSubTotal);
	$numEnvio = str_replace(",", "", $auxEnvio);
	$objResponse->assign('descuento', 'value', number_format($auxDescuentoNumerico, 2));
	$objResponse->assign('total', 'value', number_format($numSubTotal+$numEnvio-$auxDescuentoNumerico, 2));
	$objResponse->assign('divDescuento', 'innerHTML', number_format($auxDescuentoNumerico, 2));
	$objResponse->assign('divTotal', 'innerHTML', number_format($numSubTotal+$numEnvio-$auxDescuentoNumerico, 2));

	return $objResponse;
}

// Ajax
$xajax = new xajax();
$xajax->registerFunction("llenarCarritoLocal");
$xajax->registerFunction("calcularEnvio");
$xajax->registerFunction("verificarCuenta");
$xajax->registerFunction("autenticarYObtenerComprador");
$xajax->registerFunction("verificarCodCupon");
$xajax->processRequest();
$xajax->printJavascript(DIR_XAJAX);

?>