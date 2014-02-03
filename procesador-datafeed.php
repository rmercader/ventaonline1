<?PHP
// Evito CACHE
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Inicio Session
session_start();
//ini_set('display_errors', 1);
include_once('app.config.php');
include_once('sitio.config.php');
include_once(DIR_BASE.'funciones-auxiliares.php');
include(DIR_LIB.'nyiLIB.php');
include(DIR_LIB.'nyiDATA.php');
include_once(DIR_BASE.'seguridad/usuario.class.php');
include_once(DIR_BASE.'class/interfaz.class.php'); 
include_once('funciones-sitio.php');

$interfaz = new Interfaz();

$apikey = API_KEY;
 
//-----------------------------------------------------
// TRANSACTION DATAFEED
//-----------------------------------------------------
if (isset($_POST["FoxyData"])) {
 
 
	//DECRYPT (required)
	//-----------------------------------------------------
	$FoxyData_decrypted = foxycart_decrypt($_POST["FoxyData"]);
	$xml = simplexml_load_string($FoxyData_decrypted, NULL, LIBXML_NOCDATA);
 
 
	//For Each Transaction
	foreach($xml->transactions->transaction as $transaction){

		//This variable will tell us whether this is a multi-ship store or not
		$is_multiship = 0;
 
		//Get FoxyCart Transaction Information
		//Simply setting lots of helpful data to PHP variables so you can access it easily
		//If you need to access more variables, you can see some sample XML here: http://wiki.foxycart.com/v/1.0/transaction_xml_datafeed
		$transaction_id = (string)$transaction->id;
		$transaction_date = (string)$transaction->transaction_date;
		$customer_ip = (string)$transaction->customer_ip;
		$customer_id = (string)$transaction->customer_id;
		$customer_first_name = (string)$transaction->customer_first_name;
		$customer_last_name = (string)$transaction->customer_last_name;
		$customer_company = (string)$transaction->customer_company;
		$customer_email = (string)$transaction->customer_email;
		$customer_password = (string)$transaction->customer_password;
		$customer_address1 = (string)$transaction->customer_address1;
		$customer_address2 = (string)$transaction->customer_address2;
		$customer_city = (string)$transaction->customer_city;
		$customer_state = (string)$transaction->customer_state;
		$customer_postal_code = (string)$transaction->customer_postal_code;
		$customer_country = (string)$transaction->customer_country;
		$customer_phone = (string)$transaction->customer_phone;
 
 
		//This is for a multi-ship store. The shipping addresses will go in a $shipto array with the address name as the key
		$shipto = array();
		foreach($transaction->shipto_addresses->shipto_address as $shipto_address) {
			$is_multiship = 1;
			$shipto_name = (string)$shipto_address->address_name;
			$shipto[$shipto_name] = array(
				'first_name' => (string)$shipto_address->shipto_first_name,
				'last_name' => (string)$shipto_address->shipto_last_name,
				'company' => (string)$shipto_address->shipto_company,
				'address1' => (string)$shipto_address->shipto_address1,
				'address2' => (string)$shipto_address->shipto_address2,
				'city' => (string)$shipto_address->shipto_city,
				'state' => (string)$shipto_address->shipto_state,
				'postal_code' => (string)$shipto_address->shipto_postal_code,
				'country' => (string)$shipto_address->shipto_country,
				'shipping_service_description' => (string)$shipto_address->shipto_shipping_service_description,
				'subtotal' => (string)$shipto_address->shipto_subtotal,
				'tax_total' => (string)$shipto_address->shipto_tax_total,
				'shipping_total' => (string)$shipto_address->shipto_shipping_total,
				'total' => (string)$shipto_address->shipto_,
				'custom_fields' => array()
			);
 
			//Putting the Custom Fields in an array if they are there
			if (!empty($shipto_address->custom_fields)) {
				foreach($shipto_address->custom_fields->custom_field as $custom_field) {
					$shipto[$shipto_name]['custom_fields'][(string)$custom_field->custom_field_name] = (string)$custom_field->custom_field_value;
				}
			}
		}
 
		//This is setup for a single ship store
		if (!$is_multiship) {
			$shipping_first_name = (string)$transaction->shipping_first_name ? (string)$transaction->shipping_first_name : $customer_first_name;
			$shipping_last_name = (string)$transaction->shipping_last_name ? (string)$transaction->shipping_last_name : $customer_last_name;
			$shipping_company = (string)$transaction->shipping_company ? (string)$transaction->shipping_company : $customer_company;
			$shipping_address1 = (string)$transaction->shipping_address1 ? (string)$transaction->shipping_address1 : $customer_address1;
			$shipping_address2 = (string)$transaction->shipping_address2 ? (string)$transaction->shipping_address2 : $customer_address2;
			$shipping_city = (string)$transaction->shipping_city ? (string)$transaction->shipping_city : $customer_city;
			$shipping_state = (string)$transaction->shipping_state ? (string)$transaction->shipping_state : $customer_state;
			$shipping_postal_code = (string)$transaction->shipping_postal_code ? (string)$transaction->shipping_postal_code : $customer_postal_code;
			$shipping_country = (string)$transaction->shipping_country ? (string)$transaction->shipping_country : $customer_country;
			$shipping_phone = (string)$transaction->shipping_phone ? (string)$transaction->shipping_phone : $customer_phone;
			$shipto_shipping_service_description = (string)$transaction->shipto_shipping_service_description;
		}
 
		//Putting the Custom Fields in an array if they are there. These are on the top level and could be there for both single ship and multiship stores
		$custom_fields = array();
		if (!empty($transaction->custom_fields)) {
			foreach($transaction->custom_fields->custom_field as $custom_field) {
				$custom_fields[(string)$custom_field->custom_field_name] = (string)$custom_field->custom_field_value;
			}
		}
 
		//For Each Transaction Detail
		foreach($transaction->transaction_details->transaction_detail as $transaction_detail) {
			$product_name = (string)$transaction_detail->product_name;
			$product_code = (string)$transaction_detail->product_code;
			$product_quantity = (int)$transaction_detail->product_quantity;
			$product_price = (double)$transaction_detail->product_price;
			$product_shipto = (double)$transaction_detail->shipto;
			$category_code = (string)$transaction_detail->category_code;
			$product_delivery_type = (string)$transaction_detail->product_delivery_type;
			$sub_token_url = (string)$transaction_detail->sub_token_url;
			$subscription_frequency = (string)$transaction_detail->subscription_frequency;
			$subscription_startdate = (string)$transaction_detail->subscription_startdate;
			$subscription_nextdate = (string)$transaction_detail->subscription_nextdate;
			$subscription_enddate = (string)$transaction_detail->subscription_enddate;
 
			//These are the options for the product
			$transaction_detail_options = array();
			foreach($transaction_detail->transaction_detail_options->transaction_detail_option as $transaction_detail_option) {
				$product_option_name = $transaction_detail_option->product_option_name;
				$product_option_value = (string)$transaction_detail_option->product_option_value;
				$price_mod = (double)$transaction_detail_option->price_mod;
				$weight_mod = (double)$transaction_detail_option->weight_mod;

				$transaction_detail_options["$product_option_name"] = "$product_option_value";
			}
			//If you have custom code to run for each product, put it here:
			// Obtengo combinacion id_prenda-id_color-id_talle
			if(isset($transaction_detail_options["id"])){
				$componentesId = explode("-", $transaction_detail_options["id"]);
				$idPrenda = (int)$componentesId[0];
				$idColor = (int)$componentesId[1];
				$idTalle = (int)$componentesId[2];
				if($idPrenda * $idColor * $idTalle > 0){
					// Con esto valido que los ids esten bien
					$res = $interfaz->decrementarStock($idPrenda, $idColor, $idTalle, $product_quantity);
					if($res != ""){
						die("Error: $res");
					}
				}
			}
		}

		//If you have custom code to run for each order, put it here:
	}
	//All Done!
	die("foxy");

//-----------------------------------------------------
// NO POST CONTENT SENT
//-----------------------------------------------------
} 
else {
	die('No Content Received From Datafeed');
}
 
//Decrypt Data From Source
function foxycart_decrypt($src) {
    global $apikey;
	return rc4crypt::decrypt($apikey,urldecode($src));
}
 
// ======================================================================================
// RC4 ENCRYPTION CLASS
// Do not modify.
// ======================================================================================
/**
 * RC4Crypt 3.2
 *
 * RC4Crypt is a petite library that allows you to use RC4
 * encryption easily in PHP. It's OO and can produce outputs
 * in binary and hex.
 *
 * (C) Copyright 2006 Mukul Sabharwal [http://mjsabby.com]
 *     All Rights Reserved
 *
 * @link http://rc4crypt.devhome.org
 * @author Mukul Sabharwal <mjsabby@gmail.com>
 * @version $Id: class.rc4crypt.php,v 3.2 2006/03/10 05:47:24 mukul Exp $
 * @copyright Copyright &copy; 2006 Mukul Sabharwal
 * @license http://www.gnu.org/copyleft/gpl.html
 * @package RC4Crypt
 */
class rc4crypt {
	/**
	 * The symmetric encryption function
	 *
	 * @param string $pwd Key to encrypt with (can be binary of hex)
	 * @param string $data Content to be encrypted
	 * @param bool $ispwdHex Key passed is in hexadecimal or not
	 * @access public
	 * @return string
	 */
	function encrypt ($pwd, $data, $ispwdHex = 0) {
		if ($ispwdHex) $pwd = @pack('H*', $pwd); // valid input, please!
 		$key[] = '';
		$box[] = '';
		$cipher = '';
		$pwd_length = strlen($pwd);
		$data_length = strlen($data);
		for ($i = 0; $i < 256; $i++) {
			$key[$i] = ord($pwd[$i % $pwd_length]);
			$box[$i] = $i;
		}
		for ($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $key[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
		for ($a = $j = $i = 0; $i < $data_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$k = $box[(($box[$a] + $box[$j]) % 256)];
			$cipher .= chr(ord($data[$i]) ^ $k);
		}
		return $cipher;
	}
	/**
	 * Decryption, recall encryption
	 *
	 * @param string $pwd Key to decrypt with (can be binary of hex)
	 * @param string $data Content to be decrypted
	 * @param bool $ispwdHex Key passed is in hexadecimal or not
	 * @access public
	 * @return string
	 */
	function decrypt ($pwd, $data, $ispwdHex = 0) {
		return rc4crypt::encrypt($pwd, $data, $ispwdHex);
	}
}

?>