<?PHP
// Evito CACHE
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Inicio Session
session_start();
include_once('app.config.php');
include_once('sitio.config.php');
include_once(DIR_BASE.'funciones-auxiliares.php');
include(DIR_LIB.'nyiLIB.php');
include(DIR_LIB.'nyiHTML.php');
include(DIR_LIB.'nyiDATA.php');
include_once(DIR_BASE.'seguridad/usuario.class.php');
include_once(DIR_BASE.'class/interfaz.class.php'); 
include_once('funciones-sitio.php');

$interfaz = new Interfaz();
$marco = new nyiHTML('masterpage.htm');
$seccion = new nyiHTML('contacto.htm');
$errores = "";
$exitos = "";
$nombre = "";
$direccion = "";
$telefono = "";
$email = "";
$consulta = "";

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	$nombre = $_POST["nombre"];
	$direccion = $_POST["direccion"];
	$telefono = $_POST["telefono"];
	$email = $_POST["email"];
	$consulta = $_POST["consulta"];
	
	if(trim($nombre) == ""){
		$errores .= "Por favor ingrese su nombre.\n";
	}
	if(trim($direccion) == ""){
		$errores .= "Por favor ingrese su dirección.\n";
	}
	if(trim($telefono) == ""){
		$errores .= "Por favor ingrese su teléfono.\n";
	}
	if(trim($consulta) == ""){
		$errores .= "Por favor ingrese su consulta.\n";
	}
	if(!esEmailValido($email)){
		$errores .= "Por favor ingrese una dirección de email válida.\n";
	}
	
	if($errores == ""){
		$datosContacto = array(
			"nombre"=>$nombre,
			"direccion"=>$direccion,
			"telefono"=>$telefono,
			"email"=>$email,
			"consulta"=>$consulta
		);
		$res = $interfaz->procesarContactoSitio($datosContacto);
		if($res == ""){
			$_SESSION["mensaje-exito"] = "Su consulta fue enviada correctamente. Nos comunicaremos con Ud. a la brevedad.";
			$exitos = "Su consulta fue enviada correctamente. Nos comunicaremos con Ud. a la brevedad.";
			$nombre = "";
			$direccion = "";
			$telefono = "";
			$email = "";
			$consulta = "";
		}
		else {
			$errores = $res;
		}
	}
}
else {
	$_SESSION["mensaje-exito"] = "";
}

if($errores != "")
	$seccion->assign('errores', nl2br($errores));
if($exitos != "")
	$seccion->assign('exitos', $exitos);

$seccion->assign('nombre', $nombre);
$seccion->assign('direccion', $direccion);
$seccion->assign('telefono', $telefono);
$seccion->assign('email', $email);
$seccion->assign('consulta', $consulta);

$seccion->assign('imgFondo', FuncionesSitio::imagenFondoLinea(0));
$marco->assign('contenido_seccion', $seccion->fetchHTML());
$marco->assign('contactoActual', 1);
$marco->printHTML();

?>