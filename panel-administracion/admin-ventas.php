<?php

include('../app.config.php');
include('./admin.config.php');
include(DIR_BASE.'configuracion-inicial.php');
include_once(DIR_BASE.'seguridad/seguridad.class.php');

/*--------------------------------------------------------------------------
                             P E R M I S O S
  --------------------------------------------------------------------------*/

$Security = new Seguridad($Cnx);

/*--------------------------------------------------------------------------
                             M O D U L O S
  --------------------------------------------------------------------------*/
$mod_Contenido = '';
$mod_Solapa    = '';
$mod_Script    = basename($_SERVER['SCRIPT_NAME']);
$Opc = 'ventas';
$Tpl_Contenido = 'base_contenido.htm';
if(!isset($_GET['MOD'])){
	$_GET['PVEZ'] = _SI;	
}

if(isset($_GET['MOD'])){
	$Opc = $_GET['MOD'];
}

switch($Opc){
	case 'ventas':
		include("ventas/ventas.php");
		break;
	case 'busqueda-ventas':
		include("ventas/busqueda-ventas.php");
		break;
	
	case 'compradores':
		include("ventas/compradores.php");
		break;
		
	case 'invitados':
		include("ventas/invitados.php");
		break;
		
	case 'archivo-abitab':
		include("ventas/ingresar-cobros-abitab.php");
		break;
	
	default:
		include("principal.php");
		break;
}

/*--------------------------------------------------------------------------
						G E N E R O   P A G I N A
--------------------------------------------------------------------------*/
// Menu Horizontal
$Menu = new nyiMenuHor('base_menu_horizontal.htm', 170, 22);

// Menu Compradores
$Menu->AddOpcion(1, 'Compradores');
$Menu->AddOpcionLink(1, 1, "Registrados", $mod_Script, array('MOD'=>'compradores', 'PVEZ'=>_SI));
$Menu->AddOpcionLink(1, 2, 'Invitados',$mod_Script, array('MOD'=>'invitados', 'PVEZ'=>_SI));

// Menu Ventas
$Menu->AddOpcion(2, 'Ventas');
$Menu->AddOpcionLink(2, 1, 'Buscador', $mod_Script, array('MOD'=>'ventas', 'PVEZ'=>_SI));
$Menu->AddOpcionLink(2, 2, 'Ingresar archivo ABITAB', $mod_Script, array('MOD'=>'archivo-abitab'));
//$Menu->AddOpcionLink(2, 2, 'Buscador',$mod_Script, array('MOD'=>'busqueda-ventas','PVEZ'=>_SI));

// Genero html
$Contenido = new nyiHTML($Tpl_Contenido);
$Contenido->assign('SOLAPA', $mod_Solapa);
$Contenido->assign('MODCONT', $mod_Contenido);

// Modulo
$Modulo = new nyiModulo(utf8_encode('REPORTES'), 'base_modulo.htm');
$Modulo->assign('NOMSCRIPT', $mod_Script);
$Modulo->SetUsuario('Usuario: '.$_SESSION["cfgusu"]["nombre_usuario_admin"]);
$Modulo->assign('MENUES', $Menu->fetchMenu());
$Modulo->SetContenido($Contenido->fetchHTML());

// Imagen
$Perfil = $Security->GetIdPerfilUsuario($_SESSION["cfgusu"]["id_usuario_admin"]);
switch($Perfil){
	case PERFIL_ADMINISTRADOR:
		$Modulo->assign('IMAGEN_PERFIL', 'pg_sup_esq_izq.gif');
		break;
}

// Ajax
$xajax->processRequest();
$Modulo->assign('AJAX_JAVASCRIPT', $xajax->getJavascript(DIR_XAJAX_PARA_ADMIN));
$Modulo->printHTML();

?>