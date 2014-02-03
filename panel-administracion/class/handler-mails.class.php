<?PHP 

include_once(DIR_BASE.'prendas/coleccion.class.php');
include_once(DIR_BASE.'prendas/color.class.php');
include_once(DIR_BASE.'prendas/talle.class.php');
include_once(DIR_BASE.'class/class.phpmailer.php');
include_once(DIR_BASE.'class/departamento.class.php');
include_once(DIR_BASE.'ventas/comprador.class.php');
include_once(DIR_BASE.'ventas/invitado.class.php');
include_once(DIR_BASE.'ventas/venta.class.php');
include_once(DIR_BASE.'seguridad/seguridad.class.php');

/* Clase de interfaz de la logica de negocio */
class HandlerMails {
	
	private $Cnx; // AdoDBConnection
	
	// Constructor
	function HandlerMails($conexion=null){
		if($conexion == null){
			$this->Cnx = nyiCNX();
		}
		else{
			$this->Cnx = $conexion;
		}
		$this->Cnx->debug = false;
	}
	
	function enviarNuevaClaveAlComprador($emailComprador){
		// Chequear que $emailComprador este asociada una cuenta de comprador y enviarle una nueva clave generada
	}
	
	function notificarDetallesVenta($idVenta){
		// Envia a PRILI los detalles de la ventas
	}
	
	function notificarDetallesVentaAlComprador($idVenta){
		// Envia al comprador los detalles de su compra
	}
}
?>