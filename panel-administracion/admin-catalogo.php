<?php

include('../app.config.php');
include('./admin.config.php');
include(DIR_BASE.'configuracion-inicial.php');
include_once(DIR_BASE.'seguridad/seguridad.class.php');

/*--------------------------------------------------------------------------
                             P E R M I S O S
  --------------------------------------------------------------------------*/

$Security = new Seguridad($Cnx);
/*
if(!$Security->PermisoUsuarioModuloNovedades($_SESSION["cfgusu"]["id_usuario"])){
	// Redirecciono
	header("Location: inicio.php");
	exit();
}
*/
/*--------------------------------------------------------------------------
                             M O D U L O S
  --------------------------------------------------------------------------*/
$mod_Contenido = '';
$mod_Solapa    = '';
$mod_Script    = basename($_SERVER['SCRIPT_NAME']);
$Opc = 'prendas';
$Tpl_Contenido = 'base_contenido.htm';
if(!isset($_GET['MOD'])){
	$_GET['PVEZ'] = _SI;	
}

if ( ValidateModuleParameters($_GET['MOD'], 'prendas') ){
	$Opc = $_GET['MOD'];
}

$file = 'prendas/'.$Opc.'.php';
include($file);

/*--------------------------------------------------------------------------
						G E N E R O   P A G I N A
--------------------------------------------------------------------------*/
// Menu Horizontal
$Menu = new nyiMenuHor('base_menu_horizontal.htm', 150, 22);

// Menu Categorias
$Menu->AddOpcion(1, 'Categorias');
$Menu->AddOpcionLink(1, 1, utf8_encode('Nueva Categoría'), $mod_Script, array('MOD'=>'categorias-prenda', 'ACC'=>ACC_ALTA));
$Menu->AddOpcionLink(1, 2, 'Lista Categorias',$mod_Script, array('MOD'=>'categorias-prenda','PVEZ'=>_SI));

// Menu Prendas
$Menu->AddOpcion(2, 'Prendas');
$Menu->AddOpcionLink(2, 1, 'Nueva Prenda', $mod_Script, array('MOD'=>'prendas', 'ACC'=>ACC_ALTA));
$Menu->AddOpcionLink(2, 2, 'Lista Prendas',$mod_Script, array('MOD'=>'prendas','PVEZ'=>_SI));

// Menu Colores
$Menu->AddOpcion(3, 'Colores');
$Menu->AddOpcionLink(3, 1, 'Nuevo Color', $mod_Script, array('MOD'=>'colores', 'ACC'=>ACC_ALTA));
$Menu->AddOpcionLink(3, 2, 'Lista Colores',$mod_Script, array('MOD'=>'colores','PVEZ'=>_SI));

// Menu Talles
$Menu->AddOpcion(4, 'Talles');
$Menu->AddOpcionLink(4, 1, 'Nuevo Talle', $mod_Script, array('MOD'=>'talles', 'ACC'=>ACC_ALTA));
$Menu->AddOpcionLink(4, 2, 'Lista Talles',$mod_Script, array('MOD'=>'talles','PVEZ'=>_SI));

// Menu Colecciones
$Menu->AddOpcion(5, 'Colecciones');
$Menu->AddOpcionLink(5, 1, utf8_encode('Nueva Colección'), $mod_Script, array('MOD'=>'colecciones', 'ACC'=>ACC_ALTA));
$Menu->AddOpcionLink(5, 2, 'Lista Colecciones',$mod_Script, array('MOD'=>'colecciones','PVEZ'=>_SI));

// Genero html
$Contenido = new nyiHTML($Tpl_Contenido);
$Contenido->assign('SOLAPA', $mod_Solapa);
$Contenido->assign('MODCONT', $mod_Contenido);

// Modulo
$Modulo = new nyiModulo(utf8_encode('CATÁLOGO'), 'base_modulo.htm');
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