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
$seccion = new nyiHTML('actualizar-datos.htm');

// Obtenemos los departamentos
$idsDepartamentos = $interfaz->obtenerIdsDepartamentos();
$dscDepartamentos = $interfaz->obtenerDscDepartamentos();
$seccion->assign('departamento_id', $idsDepartamentos);
$seccion->assign('departamento_dsc', $dscDepartamentos);
$xajax = new xajax();
$xajax->setRequestURI('usuario-ajaxhelper.php');
$xajax->registerFunction("enviarNuevaClave");
$xajax->registerFunction("verificarCuenta");
$xajax->registerFunction("autenticarYObtenerComprador");
$xajax->registerFunction("cambiarClave");
$xajax->processRequest();
$seccion->assign('AJAX_JAVASCRIPT', $xajax->getJavascript(DIR_XAJAX));

$nombre = trim($_POST["nombre"]);
$apellido = trim($_POST["apellido"]);
$email = trim($_POST["email"]);
$direccion = trim($_POST["direccion"]);
$idDepartamento = trim($_POST["id_departamento"]);
$ciudad = trim($_POST["ciudad"]);
$codPostal = trim($_POST["codigo_postal"]);
$telefono = trim($_POST["telefono"]);
$clave = $_POST["clave"];
$idComprador = $_POST["id_comprador"];
$errores = "";
$exitos = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if($email == ''){
		$errores .= "Por favor ingrese su email.\n";
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
	if($clave == ""){
		$errores .= "Por favor ingrese su contraseña.\n";
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

$seccion->assign('nombre', $nombre);
$seccion->assign('apellido', $apellido);
$seccion->assign('email', $email);
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