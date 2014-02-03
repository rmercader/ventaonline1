<?PHP

// includes
include_once(DIR_BASE.'class/table.class.php');

class LineaPrenda extends Table {

	// ------------------------------------------------
	//  Crea y configura conexion
	// ------------------------------------------------
	function LineaPrenda($DB){
		// Conexion
		$this->Table($DB, 'linea');
	}
	
	// Retorna el combo de identificadores ordenados segun nombre
	function GetComboIds($Todos=false, $IdT=0){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT id_linea FROM linea ORDER BY nombre_linea");
		
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($IdT),$Col);
		}
		return($Col);
	}
	
	// ------------------------------------------------
	// Devuelvo array de detalles para combo
	// ------------------------------------------------
	function GetComboNombres($Todos=false,$NomT='Todos'){
		$Aux = $this->DB;
		$Col = $Aux->getCol("SELECT nombre_linea FROM linea ORDER BY nombre_linea");
		// Si hay que agregar
		if ($Todos){
			if (is_array($Col))
				$Col = array_merge(array($NomT),$Col);
		}
		return($Col);
	}
	
	function nombre($idLinea){
		return $this->DB->getOne("SELECT nombre_linea FROM linea WHERE id_linea = {$idLinea}");
	}
}
?>