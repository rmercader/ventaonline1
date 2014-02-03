<?PHP

// Constantes para rutas
define('DIR_PUBLIC', dirname(__FILE__) . "/");
define('DIR_BASE', DIR_PUBLIC . "panel-administracion/");
define('DIR_LIB', DIR_BASE . "nyi/");
define('SMARTY_APP_DIR', "D:\\Smarty\\Prili\\");
//define('SMARTY_APP_DIR', DIR_PUBLIC . "smarty/"); // Produccion

define('DIR_HTTP_PUBLICA', 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].'/prili/');
//define('DIR_HTTP_PUBLICA', 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].'/nuevo/'); // Produccion
define('DIR_HTTP', DIR_HTTP_PUBLICA.'panel-administracion/');
define('DIR_XAJAX', 'panel-administracion/xajax/');
define('DIR_XAJAX_PARA_ADMIN', '../' . DIR_XAJAX);
define('DIR_ACTIVACION', DIR_HTTP . 'activar_cuenta_usuario.php');
define('LOG_USUARIOS', DIR_PUBLIC . "logs/log_usuarios.log");
define('LOG_ERRORES', DIR_PUBLIC . "logs/errores.log");
define('LOG_SISTEMA', DIR_PUBLIC . "logs/sistema.log");
define('LOG_ENTERO', DIR_PUBLIC ."logs/entero.log");

// Constantes para la base de datos

define('DB_PROVIDER', 'mysql');
define('DB_HOST', 'localhost');
define('DB_USER', 'prili_user');
define('DB_PASSWORD', 'prili');
define('DB_DATABASE', 'prili');
/* PRODUCCION 
define('DB_PROVIDER', 'mysql');
define('DB_HOST', 'localhost');
define('DB_USER', 'prili_dbprili');
define('DB_PASSWORD', '!dbpr1ly11#');
define('DB_DATABASE', 'prili_sitio2012');
*/

include(DIR_BASE.'constantes.php');

?>
