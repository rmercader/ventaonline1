/* manejo-venta.js */
/*
	Aqui se maneja toda la logica de la pagina con javascript, referente al checkout para compra con medios
	de pago locales.
*/

// Foco en el campo clave
function focusClave(){
	$('#clave').focus();
}

// Carga en el sistema el contenido que tiene la bolsa de compras del foxycart
function obtenerContenidoBolsaCompras(){
	jQuery.getJSON('https://'+storedomain+'/cart?'+fcc.session_get()+'&output=json&callback=?', function(cart) {
		xajax_llenarCarritoLocal(cart.products);
	});
}

// Calcula el costo de envio segun el departamento
function calcularCostoEnvio(){
	var id_departamento = $("#id_departamento").attr("value");
	var subtotal = $("#subtotal").attr("value");
	xajax_calcularEnvio(id_departamento, subtotal);
}

// Oculta todos los mensajes de error reportados en la pagina
function limpiarMensajesError(){
	$('#errEmail').css("display", "none");
	$('#errEmailConf').css("display", "none");
	$('#errClave').css("display", "none");
	$('#errClaveConf').css("display", "none");
	$('#errNombre').css("display", "none");
	$('#errApellido').css("display", "none");
	$('#errDireccion').css("display", "none");
	$('#errDepartamento').css("display", "none");
	$('#errCiudad').css("display", "none");
	$('#errCP').css("display", "none");
	$('#errTelefono').css("display", "none");
	//limpio tambien lo correspondiente al cupon de descuento...
	//$('#errCupon').value("");
	//$('#errCupon').css("display", "none");
	//$('#aceptCupon').css("display", "none");
}

// Verifica que la combinacion email,clave pertenezca a una cuenta y en caso afirmativo llena los campos con los datos de la cuenta
function verificarComprador(){
	limpiarMensajesError();
	var esInvitado = $('input[name=is_anonymous]:checked', '#frmCheckout').val();
	var clave = trim($("#clave").attr("value"));
	var idComprador = trim($("#id_comprador").attr("value"))*1;
	if(esInvitado == 0){
		if(clave == ""){
			$('#errClave').css("display", "block");
		}
		else if(idComprador > 0){
			$("#login_ajax").css("display", "block");
			xajax_autenticarYObtenerComprador($("#email").attr("value"), clave);
		}
	}
}

function habilitarDepartamento(){
	document.getElementById("id_departamento").disabled = false; 
}

// Handlers varios cuando la pagina ya termino de cargarse
$(document).ready(function(){
	
	// Calcula el costo de envio cuando cambia el departamento 
	$("#id_departamento").change(function(){
		calcularCostoEnvio();
	});
	
	// Click en confirmar la compra, validaciones varias y submit si todo OK
	$("#btn-confirm").click(function(){
		limpiarMensajesError();
		var email = trim($("#email").attr("value"));
		var emailconf = trim($("#emailconf").attr("value"));
		var nombre = trim($("#nombre").attr("value"));
		var apellido = trim($("#apellido").attr("value"));
		var direccion = trim($("#direccion").attr("value"));
		var id_departamento = trim($("#id_departamento").attr("value"));
		var ciudad = trim($("#ciudad").attr("value"));
		var cPostal = trim($("#codigo_postal").attr("value"));
		var telefono = trim($("#telefono").attr("value"));
		var esInvitado = $('input[name=is_anonymous]:checked', '#frmCheckout').val();
		var clave = trim($("#clave").attr("value"));
		var claveConf = trim($("#clave_conf").attr("value"));
		var idComprador = trim($("#id_comprador").attr("value"))*1;
		
		// Validaciones
		var errores = false;
		if(email == ''){
			$('#errEmail').css("display", "block");
			errores = true;
		}
		else{ 
			// Si no hay identificada una cuenta y los emails no coinciden
			if(idComprador == 0 && email != emailconf){
				$('#errEmailConf').css("display", "block");
				errores = true;
			}
		}
		
		// Si se esta creando o usando una cuenta existente
		if(esInvitado == 0){
			if(clave == "" && $('#errClave').css("display") == "block"){
				$('#errClave').css("display", "block");
				errores = true;
			}
			else if(idComprador == 0 && claveConf != clave){
				// Cuando no hay identificada una cuenta y las claves no coinciden
				$('#errClaveConf').css("display", "block");
				errores = true;
			}
		}
		if(nombre == ''){
			$('#errNombre').css("display", "block");
			errores = true;
		}
		if(apellido == ''){
			$('#errApellido').css("display", "block");
			errores = true;
		}
		if(direccion == ''){
			$('#errDireccion').css("display", "block");
			errores = true;
		}
		if(id_departamento == 0){
			$('#errDepartamento').css("display", "block");
			errores = true;
		}
		if(ciudad == ''){
			$('#errCiudad').css("display", "block");
			errores = true;
		}
		if(cPostal == ''){
			$('#errCP').css("display", "block");
			errores = true;
		}
		if(telefono == ''){
			$('#errTelefono').css("display", "block");
			errores = true;
		}
		
		if(!errores){
			$("#frmCheckout").submit();
		}
	});
	
	// OnBlur del email, tiene que verificar existencia de cuenta
	$("#email").blur(function(){
		limpiarMensajesError();
		var email = trim($("#email").attr("value"));
		var esInvitado = $('input[name=is_anonymous]:checked', '#frmCheckout').val();
		//alert(esInvitado);
		if(email != ""){
			$("#login_ajax").css("display", "block");
			xajax_verificarCuenta(email, esInvitado);
		}
	});
	
	// OnBlur del cupon, tiene que validar el cupon
	$("#cupon").blur(function(){
		var auxCupon = trim($("#cupon").attr("value"));
		var auxSubTotal = $("#subtotal").attr("value");
		var auxEnvio = $("#envio").attr("value");
		if(auxCupon != ""){
			xajax_verificarCodCupon(auxCupon, auxSubTotal, auxEnvio);
		}
	});

	// OnBlur de la clave, tiene que autenticar la cuenta
	$("#clave").blur(function(){
		limpiarMensajesError();
		verificarComprador();
	});
	
	// OnClick de boton Continuar, tiene que autenticar la cuenta
	$("#btn-continuar").click(function(){
		limpiarMensajesError();
		verificarComprador();
	});
	
	// Desactiva el indicador ajax
	$("#email").focus(function(){
		$("#login_ajax").css("display", "none");
	});
	
	// Seleccionan crear nueva cuenta o usar cuenta existente
	$("#is_anonymous_0").click(function(){
		var idComprador = trim($("#id_comprador").attr("value"))*1;
		$("#divClave").css("display", "block");
		if(idComprador == 0){
			// No hay identificada una cuenta, se le pide confirmacion tanto del mail como de la clave que va a crearse
			$("#divClaveConf").css("display", "block");
			$("#liMailConf").css("display", "block");
		}
		else {
			// Hay identificada una cuenta, no tiene que confirmar mail
			$("#liMailConf").css("display", "none");
		}
	});
	
	// Seleccionan comprar como invitado, tiene que confirmar solo el mail, no se asocia clave alguna
	$("#is_anonymous_1").click(function(){
		$("#liMailConf").css("display", "block");
		$("#divClave").css("display", "none");
		$("#divClaveConf").css("display", "none");
		$('#errClave').css("display", "none");
	});
	
	// OnBlur de los campos
	$("#email").blur(function(){
		var email = trim($("#email").attr("value"));
		if(email != ""){
			$('#errEmail').css("display", "none");
		}
	});
	$("#emailconf").blur(function(){
		var emailconf = trim($("#emailconf").attr("value"));
		if(emailconf != ""){
			$('#errEmailConf').css("display", "none");
		}
	});
	$("#nombre").blur(function(){
		var nombre = trim($("#nombre").attr("value"));
		if(nombre != ""){
			$('#errNombre').css("display", "none");
		}
	});
	$("#apellido").blur(function(){
		var apellido = trim($("#apellido").attr("value"));
		if(apellido != ""){
			$('#errApellido').css("display", "none");
		}
	});
	$("#direccion").blur(function(){
		var direccion = trim($("#direccion").attr("value"));
		if(direccion != ""){
			$('#errDireccion').css("display", "none");
		}
	});
	$("#id_departamento").change(function(){
		var id_departamento = trim($("#id_departamento").attr("value"));
		if(id_departamento != 0){
			$('#errDepartamento').css("display", "none");
		}
	});
	$("#ciudad").blur(function(){
		var ciudad = trim($("#ciudad").attr("value"));
		if(ciudad != ""){
			$('#errCiudad').css("display", "none");
		}
	});	
	$("#codigo_postal").blur(function(){
		var cPostal = trim($("#codigo_postal").attr("value"));
		if(cPostal != ""){
			$('#errCP').css("display", "none");
		}
	});		
	$("#telefono").blur(function(){
		var telefono = trim($("#telefono").attr("value"));
		if(telefono != ""){
			$('#errTelefono').css("display", "none");
		}
	});	
	
	// Si hay que desplegar mensaje de error al cargar la pagina
	if(trim($("#errores").attr("value")) != ""){
		TINY.box.show({
			html: $("#errores").attr("value"),
			boxid: "boxid"
		});
		$("#boxid").css("font-weight", "bold");
		$("#boxid").css("color", "#D03B39");
		$("#boxid").css("background-color", "#F7E0DF");
	}
	
	// Si hay que desplegar mensaje de exito al cargar la pagina
	if(trim($("#exitos").attr("value")) != ""){
		TINY.box.show({
			url: "mensaje-exito.php",
			boxid: "boxid"
		});
		$("#boxid").css("background-color", "#edfced");
		/*
		$("#boxid").css("font-weight", "bold");
		$("#boxid").css("color", "#D03B39");
		$("#boxid").css("background-color", "#edfced");*/
	}

	$(".medio-pago").each(function(item, vble) { 
		//vble.checked = false;
	});

	/*$("#medio_pago_oca").click(function(){
		$(".info_oca").each(function(item, vble) { 
			vble.display = "block";
		});
	});*/

	$("#medio_pago_oca").click(function(){
		$("#info_oca").css("display", "block");
	});

	$("#medio_pago_abitab").click(function(){
		$("#info_oca").css("display", "none");
	});
;});