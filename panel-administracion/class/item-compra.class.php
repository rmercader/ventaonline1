<?PHP 

class ItemCompra {
	var $idPrenda = 0;
	var $idTalle = 0;
	var $idColor = 0;
	var $cantidad = 0;
	var $descripcion;
	var $precio = 0;
	var $subtotal = 0;
	
	function ItemCompra($idPrenda, $idTalle, $idColor, $cantidad, $dsc, $precio, $subtotal){
		if(is_numeric($idPrenda) && $idPrenda > 0){
			$this->idPrenda = $idPrenda;
		}
		if(is_numeric($idTalle) && $idTalle > 0){
			$this->idTalle = $idTalle;
		}
		if(is_numeric($idColor) && $idColor > 0){
			$this->idColor = $idColor;
		}
		if(is_numeric($cantidad) && $cantidad > 0){
			$this->cantidad = $cantidad;
		}
		if(is_numeric($precio) && $precio > 0){
			$this->precio = $precio;
		}
		if(is_numeric($subtotal) && $subtotal > 0){
			$this->subtotal = $subtotal;
		}
		$this->descripcion = $dsc;
	}
}

?>