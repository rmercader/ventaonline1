function actualizarLinkBolsa(){
	jQuery.getJSON('https://'+storedomain+'/cart?'+fcc.session_get()+'&output=json&callback=?', function(cart) {
		if(cart.product_count > 0){
			$("#linkbolsa").html("BOLSA DE COMPRAS (" + cart.product_count + ")");
		}
		else {
			$("#linkbolsa").html("BOLSA DE COMPRAS");
		}
	});
}