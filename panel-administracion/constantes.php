<?PHP

// Lineas de prendas
define('LINEA_DAMA', 1);
define('LINEA_HOMBRE', 2);
define('LINEA_INFANTIL', 3);

// Constantes para links en modulos
define('LNK_NADA', 'nada');

// Constantes para acciones
define('ACC_GRID','G');
define('ACC_PDF','F');
define('ACC_ALTA','A');
define('ACC_MODIFICACION','M');
define('ACC_CONSULTA','C');
define('ACC_SELECCIONAR','L');
define('ACC_BAJA','B');
define('ACC_POST','S');
define('ACC_VER','X');
define('ACC_ANULACION', 'N');

// Constantes para las categorias de prendas
define('LARGO_FOTO_CATEGORIA_PRENDA', 678);
define('ANCHO_FOTO_CATEGORIA_PRENDA', 348);
define('DIR_HTTP_FOTOS_CATEGORIAS_PRENDAS', DIR_HTTP.'prendas/categorias/');
define('DIR_FOTOS_CATEGORIAS_PRENDAS', DIR_BASE.'prendas/categorias/');

// Constantes para las prendas
define('PRENDAS_POR_PAGINA', 6);
define('DIR_HTTP_FOTOS_PRENDAS', DIR_HTTP.'prendas/fotos/');
define('DIR_FOTOS_PRENDAS', DIR_BASE.'prendas/fotos/');
define('LARGO_THUMBNAIL_PRENDA', 85);
define('ANCHO_THUMBNAIL_PRENDA', 113);
define('LARGO_PREVIEW_PRENDA', 190);
define('ANCHO_PREVIEW_PRENDA', 224);
define('LARGO_FOTO_PRENDA', 424);
define('ANCHO_FOTO_PRENDA', 500);
define('LARGO_IMG_COLOR', 215);
define('ANCHO_IMG_COLOR', 128);
define('LARGO_THU_COLOR', 26);
define('ANCHO_THU_COLOR', 20);

// OCA
define('MEDIO_PAGO_OCA', 1);
define('NRO_COM_OCA', '122652');
define('NRO_TERM_OCA', '122652SD');
define('MONEDA_OCA', 858);
define('TIPO_COMPRA_OCA', 0);

// ABITAB
define('MEDIO_PAGO_ABITAB', 0);
define('COD_CLIENTE_ABITAB', 'PRI');

// Constantes Generales
define('USUARIO_WS', 'webservices');
define('CLAVE_USUARIO_WS', 'wsprili');
define('URL_GENERADOR_CODIGO_BARRAS', DIR_HTTP . 'generar-codigo-barras.php');
define('URL_PROCESADOR_ABITAB', DIR_HTTP . 'ventas/procesador-abitab.php');
define('API_KEY', "DAQ5QLtZ8gGkFtB5wdqGwCqUMCG7IWsgm8f7Pz1MFx3fQyehBvN325RFBpCa");
define('URL_WS_COTIZACION', "http://www.webservicex.net/CurrencyConvertor.asmx?wsdl");
define('MONEDA_BASE', 'UYU'); // Pesos uruguayos
define('MONEDA_TRANSACCIONES', 'USD'); // Dolares americanos
define('ORIENTACION_HORIZONTAL', 'horizontal');
define('ORIENTACION_VERTICAL', 'vertical');
define('MAIL_HOST', 'mail.prili.net');
define('CASILLA_NO_REPLY', 'noreply@prili.net');
define('CASILLA_NOTIFICACION_VENTA', 'prili@prili.net');
define('CASILLA_NOTIFICACION_CONTACTO', 'prili@prili.net');
define('_SI','S');
define('_NO','N');
define('_SIN','Si');
define('_NON','No');
define('ID_SN',_SI.'|'._NO);
define('NOM_SN','Si|No');
define('CANT_DEC', 6);
define('ID_IDIOMA_ADMIN', 2);
define('ALTURA_EDITOR', '300');
define('CREDENCIALES_CLIENTE', "CREDENCIALES_CLIENTE");
define('COOKIE_ID_CLIENTE', "COOKIE_ID_CLIENTE");

// Perfiles de usuario
define('PERFIL_ADMINISTRADOR', 1);
define('PERFIL_CLIENTE', 2);

// Error Level
define('_ERROR','ERROR');
define('_OK','OK');

// Agenda
$DIASEM = array('Domigo','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado');
$HORAS  = array('00:00','01:00','02:00','03:00','04:00','05:00','06:00','07:00','08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00','21:00','22:00','23:00','24:00');

// Paneles por defecto
$Tpl_Panel      = 'base_panel.htm';
$Tpl_Calendario = 'base_calendario.htm';
$Tpl_Grid       = 'base_grid.htm';
$Tpl_Menu       = 'base_menu.htm';

// Temas HTML
$TEMASHTML_id  = array('estilo01');
$TEMASHTML_nom = array('Tema por defecto');

// Variables por defecto
$ESTILO_HTML = 'estilo01';
$Reg_Pag = 20;
$Reg_Pag_bt = 15;
define('TAM_PAGINA', $Reg_Pag);

?>
