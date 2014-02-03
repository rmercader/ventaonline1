<?PHP
/*--------------------------------------------------------------------------
   Archivo:home.php
   Descripcion: Home del sistema
  --------------------------------------------------------------------------*/   

include_once('../app.config.php');
include('./admin.config.php');
include_once(DIR_BASE.'configuracion-inicial.php');
include_once(DIR_BASE.'seguridad/seguridad.class.php');

/*--------------------------------------------------------------------------
                             P E R M I S O S
  --------------------------------------------------------------------------*/

$Security = new Seguridad($Cnx);

// -------------------------------------------------------------------------------
//                                Genero Pagina
// -------------------------------------------------------------------------------
// Genero html
$mod_Contenido = '';
$mod_Solapa    = '';
$mod_Script    = basename($_SERVER['SCRIPT_NAME']);
$Opc = 0;
$Tpl_Contenido = 'base_nada.htm';
include('./principal.php');
$Contenido = new nyiHTML($Tpl_Contenido);
$Contenido->assign('MODCONT',$mod_Contenido);

// Modulo
$Modulo = new nyiModulo('', 'base_modulo.htm');
$Modulo->assign('NOMSCRIPT',basename($_SERVER['SCRIPT_NAME']));
$Modulo->SetUsuario($_SESSION["cfgusu"]["nombre_usuario_admin"]);
$Modulo->SetContenido($Contenido->fetchHTML());

// Ajax
$xajax->processRequest();
$Modulo->assign('AJAX_JAVASCRIPT', $xajax->getJavascript('../'.DIR_XAJAX));

$Modulo->printHTML();
?>