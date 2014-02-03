/* manejo-venta.js */
/*
	Aqui se maneja toda la logica de la pagina con javascript, referente a la modificacion de datos de un usuario registrado
*/

// Foco en el campo clave
function focusClave(){
	$('#clave').focus();
}

// Oculta todos los mensajes de error reportados en la pagina
function limpiarMensajesError(){
	$('#errEmail').css("display", "none");
	$('#errClave').css("display", "none");
	$('#errNombre').css("display", "none");
	$('#errApellido').css("display", "none");
	$('#errDireccion').css("display", "none");
	$('#errDepartamento').css("display", "none");
	$('#errCiudad').css("display", "none");
	$('#errCP').css("display", "none");
	$('#errTelefono').css("display", "none");
}

// Verifica que la combinacion email,clave pertenezca a una cuenta y en caso afirmativo llena los campos con los datos de la cuenta
function verificarComprador(){
	limpiarMensajesError();
	var clave = trim($("#clave").attr("value"));
	var idComprador = trim($("#id_comprador").attr("value"))*1;
	if(clave == ""){
		$('#errClave').css("display", "block");
	}
	else {
		$("#login_ajax").css("display", "block");
		xajax_autenticarYObtenerComprador($("#email").attr("value"), clave);
	}
}

function enviarContrasena(){
	if(confirm("Esta acción provocará que su contraseña actual sea cambiada por una nueva generada automáticamente, la cual será enviado a la casilla de correo de su cuenta. ¿Desea continuar?")){
		$("#login_ajax").css("display", "block");
		xajax_enviarNuevaClave($("#email").attr("value"));
	}
}

function abrirCambioContrasena(){
	TINY.box.show({'url':'cambio-clave.php', 'width': 170, 'height':170});
}

// Handlers varios cuando la pagina ya termino de cargarse
$(document).ready(function(){
	
	// Click en confirmar la compra, validaciones varias y submit si todo OK
	$("#btn-confirm").click(function(){
		limpiarMensajesError();
		var email = trim($("#email").attr("value"));
		var nombre = trim($("#nombre").attr("value"));
		var apellido = trim($("#apellido").attr("value"));
		var direccion = trim($("#direccion").attr("value"));
		var id_departamento = trim($("#id_departamento").attr("value"));
		var ciudad = trim($("#ciudad").attr("value"));
		var cPostal = trim($("#codigo_postal").attr("value"));
		var telefono = trim($("#telefono").attr("value"));
		var clave = trim($("#clave").attr("value"));
		var idComprador = trim($("#id_comprador").attr("value"))*1;
		
		// Validaciones
		var errores = false;
		if(email == ''){
			$('#errEmail').css("display", "block");
			errores = true;
		}
		if(clave == ""){
			$('#errClave').css("display", "block");
			errores = true;
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
			$("#frmDatos").submit();
		}
	});
	
	// OnBlur del email, tiene que verificar existencia de cuenta
	$("#email").blur(function(){
		limpiarMensajesError();
		var email = trim($("#email").attr("value"));
		var esInvitado = $('input[name=is_anonymous]:checked', '#frmDatos').val();
		//alert(esInvitado);
		if(email != ""){
			$("#login_ajax").css("display", "block");
			xajax_verificarCuenta(email, esInvitado);
		}
	});
	
	// OnBlur de la clave, tiene que autenticar la cuenta
	$("#clave").blur(function(){
		if($("#clave").attr("value") != ""){
			limpiarMensajesError();
			verificarComprador();
		}
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
	
	$("#continuarMail").click(function(){
		$("#email").blur();
	});
	
	// OnBlur de los campos
	$("#email").blur(function(){
		var email = trim($("#email").attr("value"));
		if(email != ""){
			$('#errEmail').css("display", "none");
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
	}
});

function cambiarClave(){
	var email = trim($("#email").attr("value"));
	var clave_actual = trim($("#clave_actual").attr("value"));
	var clave_nueva = trim($("#clave_nueva").attr("value"));
	var clave_nueva_conf = trim($("#clave_nueva_conf").attr("value"));
	if(clave_nueva == ""){
		alert("Por favor ingrese la contraseña nueva.");
	}
	else if(clave_nueva != clave_nueva_conf){
		alert("La contraseña nueva y su confirmación no coinciden.");
	}
	else {
		xajax_cambiarClave(email, clave_actual, clave_nueva, clave_nueva_conf);
	}
}

function cambiarClaveExito(){
	alert("Su contraseña fue cambiada correctamente.");
	$("#clave").val("");
	$("#clave").focus();
	$("#divDatos").css("display", "none");
	$("#btn-confirm").css("display", "none");
	TINY.box.hide();
}

function cambiarClaveError(error){
	alert(error);
}