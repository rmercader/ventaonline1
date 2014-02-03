<?
/**
 * FoxyCart Test XML Generator
 * 
 * @link http://wiki.foxycart.com/integration:misc:test_xml_post
 * @version 0.6a
 */
/*
	DESCRIPTION: =================================================================
	The purpose of this file is to help you set up and debug your FoxyCart XML DataFeed scripts.
	It's designed to mimic FoxyCart.com and send encrypted and encoded XML to a URL of your choice.
	It will print out the response that your script gives back, which should be "foxy" if successful.
	
	USAGE: =======================================================================
	- Place this file somewhere on your server.
	- Edit the $myURL to the URL where your XML processing script is located.
	- Edit the $myKey to match the key you put in your FoxyCart admin.
	- Edit the $XMLOutput if you have specific data you'd like to test.
	- Save.
	- Load this file in your browser. It will send XML to your script just like FoxyCart would
	  after an order on your store, and will output what your script returns.
	- Test until you get your script working properly.
	
	REQUIREMENTS: ================================================================
	- PHP
	- cURL support in PHP
*/

// ======================================================================================
// CHANGE THIS DATA:
// Set the URL you want to post the XML to.
// Set the key you entered in your FoxyCart.com admin.
// Modify the XML below as necessary.  DO NOT modify the structure, just the data
// ======================================================================================
$myURL = 'http://www.prili.net/nuevo/procesador-datafeed.php';
$myKey = 'DAQ5QLtZ8gGkFtB5wdqGwCqUMCG7IWsgm8f7Pz1MFx3fQyehBvN325RFBpCa';

// You can change the test data below if you'd like to test specific fields.
// For example, you may want to set it up to mirror 
$XMLOutput = <<<XML
<?xml version='1.0' encoding='UTF-8' standalone='yes'?>
<foxydata>
    <store_version><![CDATA[1.0]]></store_version>
    <transactions>
        <transaction>
            <id><![CDATA[17172427]]></id>
            <store_id><![CDATA[17646]]></store_id>
            <store_version><![CDATA[1.0]]></store_version>
            <is_test><![CDATA[1]]></is_test>
            <is_hidden><![CDATA[0]]></is_hidden>
            <data_is_fed><![CDATA[0]]></data_is_fed>
            <transaction_date><![CDATA[2012-11-18 22:51:07]]></transaction_date>
            <payment_type><![CDATA[purchase_order]]></payment_type>
            <payment_gateway_type><![CDATA[purchase_order]]></payment_gateway_type>
            <processor_response><![CDATA[Orden de compra]]></processor_response>
            <processor_response_details></processor_response_details>
            <purchase_order><![CDATA[POTEST001]]></purchase_order>
            <cc_number_masked><![CDATA[]]></cc_number_masked>
            <cc_type><![CDATA[]]></cc_type>
            <cc_exp_month><![CDATA[]]></cc_exp_month>
            <cc_exp_year><![CDATA[]]></cc_exp_year>
            <cc_start_date_month><![CDATA[]]></cc_start_date_month>
            <cc_start_date_year><![CDATA[]]></cc_start_date_year>
            <cc_issue_number><![CDATA[]]></cc_issue_number>
            <minfraud_score><![CDATA[0]]></minfraud_score>
            <paypal_payer_id><![CDATA[]]></paypal_payer_id>
            <customer_id><![CDATA[5437710]]></customer_id>
            <is_anonymous><![CDATA[0]]></is_anonymous>
            <customer_first_name><![CDATA[Rodrigo]]></customer_first_name>
            <customer_last_name><![CDATA[Mercader]]></customer_last_name>
            <customer_company><![CDATA[Narthex Its]]></customer_company>
            <customer_address1><![CDATA[1 Main St]]></customer_address1>
            <customer_address2><![CDATA[Maldonado 1818]]></customer_address2>
            <customer_city><![CDATA[San Jose]]></customer_city>
            <customer_state><![CDATA[CA]]></customer_state>
            <customer_postal_code><![CDATA[95131]]></customer_postal_code>
            <customer_country><![CDATA[US]]></customer_country>
            <customer_phone><![CDATA[23361610]]></customer_phone>
            <customer_email><![CDATA[rmerca_1343243580_per@narthex.com.uy]]></customer_email>
            <customer_ip><![CDATA[186.48.4.208]]></customer_ip>
            <shipping_first_name><![CDATA[Rodrigo]]></shipping_first_name>
            <shipping_last_name><![CDATA[Mercader]]></shipping_last_name>
            <shipping_company><![CDATA[Narthex Its]]></shipping_company>
            <shipping_address1><![CDATA[1 Main St]]></shipping_address1>
            <shipping_address2><![CDATA[Maldonado 1818]]></shipping_address2>
            <shipping_city><![CDATA[San Jose]]></shipping_city>
            <shipping_state><![CDATA[CA]]></shipping_state>
            <shipping_postal_code><![CDATA[95131]]></shipping_postal_code>
            <shipping_country><![CDATA[US]]></shipping_country>
            <shipping_phone><![CDATA[23361610]]></shipping_phone>
            <shipto_shipping_service_description><![CDATA[]]></shipto_shipping_service_description>
            <product_total><![CDATA[35.3]]></product_total>
            <tax_total><![CDATA[0]]></tax_total>
            <shipping_total><![CDATA[10]]></shipping_total>
            <order_total><![CDATA[45.3]]></order_total>
            <receipt_url><![CDATA[http://prili.foxycart.com/receipt?id=f625fce669422a37b7925d6b1c537630]]></receipt_url>
            <taxes></taxes>
            <discounts></discounts>
            <customer_password><![CDATA[4007aa7af3f6c385d5c725aed089a751c1ac9c97]]></customer_password>
            <customer_password_salt><![CDATA[JAWStbuY3jFAUD6H]]></customer_password_salt>
            <customer_password_hash_type><![CDATA[sha1_salted_suffix]]></customer_password_hash_type>
            <customer_password_hash_config><![CDATA[16]]></customer_password_hash_config>
            <custom_fields>
                <custom_field>
                    <custom_field_name><![CDATA[minimop]]></custom_field_name>
                    <custom_field_value><![CDATA[500.00]]></custom_field_value>
                    <custom_field_is_hidden><![CDATA[1]]></custom_field_is_hidden>
                </custom_field>
                <custom_field>
                    <custom_field_name><![CDATA[minimod]]></custom_field_name>
                    <custom_field_value><![CDATA[25.25]]></custom_field_value>
                    <custom_field_is_hidden><![CDATA[1]]></custom_field_is_hidden>
                </custom_field>
            </custom_fields>
            <transaction_details>
                <transaction_detail>
                    <product_name><![CDATA[Bikini Nicolina]]></product_name>
                    <product_price><![CDATA[35.3]]></product_price>
                    <product_quantity><![CDATA[1]]></product_quantity>
                    <product_weight><![CDATA[0.000]]></product_weight>
                    <product_code><![CDATA[42]]></product_code>
                    <image><![CDATA[]]></image>
                    <url><![CDATA[]]></url>
                    <length><![CDATA[0]]></length>
                    <width><![CDATA[0]]></width>
                    <height><![CDATA[0]]></height>
                    <downloadable_url><![CDATA[]]></downloadable_url>
                    <sub_token_url><![CDATA[]]></sub_token_url>
                    <subscription_frequency><![CDATA[]]></subscription_frequency>
                    <subscription_startdate><![CDATA[0000-00-00]]></subscription_startdate>
                    <subscription_nextdate><![CDATA[0000-00-00]]></subscription_nextdate>
                    <subscription_enddate><![CDATA[0000-00-00]]></subscription_enddate>
                    <is_future_line_item><![CDATA[0]]></is_future_line_item>
                    <shipto><![CDATA[]]></shipto>
                    <category_description><![CDATA[Default for all products]]></category_description>
                    <category_code><![CDATA[DEFAULT]]></category_code>
                    <product_delivery_type><![CDATA[flat_rate]]></product_delivery_type>
                    <transaction_detail_options>
                        <transaction_detail_option>
                            <product_option_name><![CDATA[id]]></product_option_name>
                            <product_option_value><![CDATA[42-30-7]]></product_option_value>
                            <price_mod><![CDATA[0]]></price_mod>
                            <weight_mod><![CDATA[0.000]]></weight_mod>
                        </transaction_detail_option>
                        <transaction_detail_option>
                            <product_option_name><![CDATA[color]]></product_option_name>
                            <product_option_value><![CDATA[30 - coral]]></product_option_value>
                            <price_mod><![CDATA[0]]></price_mod>
                            <weight_mod><![CDATA[0.000]]></weight_mod>
                        </transaction_detail_option>
                        <transaction_detail_option>
                            <product_option_name><![CDATA[talle]]></product_option_name>
                            <product_option_value><![CDATA[1]]></product_option_value>
                            <price_mod><![CDATA[0]]></price_mod>
                            <weight_mod><![CDATA[0.000]]></weight_mod>
                        </transaction_detail_option>
                    </transaction_detail_options>
                </transaction_detail>
            </transaction_details>
            <shipto_addresses></shipto_addresses>
            <attributes></attributes>
        </transaction>
    </transactions>
</foxydata>
XML;



// ======================================================================================
// YOU'RE DONE.  DO NOT MODIFY BELOW THIS LINE.
// The code below this line should not be modified unless you have a good reason to do so.
// ======================================================================================

// ======================================================================================
// ENCRYPT YOUR XML
// Modify the include path to go to the rc4crypt file.
// ======================================================================================
$XMLOutput_encrypted = rc4crypt::encrypt($myKey,$XMLOutput);
$XMLOutput_encrypted = urlencode($XMLOutput_encrypted);


// ======================================================================================
// POST YOUR XML TO YOUR SITE
// Do not modify.
// ======================================================================================
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $myURL);
curl_setopt($ch, CURLOPT_POSTFIELDS, array("FoxyData" => $XMLOutput_encrypted));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
// Shared hosting users on GoDaddy or other hosts may need to uncomment the following lines:
// curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
// curl_setopt($ch, CURLOPT_PROXY,"http://64.202.165.130:3128"); // Replace this IP with whatever your host specifies.
// End shared hosting options
$response = curl_exec($ch);
curl_close($ch);


header("content-type:text/plain");
print $response;




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

/**
 * RC4 Class
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
	function encrypt ($pwd, $data, $ispwdHex = 0)
	{
		if ($ispwdHex)
			$pwd = @pack('H*', $pwd); // valid input, please!

		$key[] = '';
		$box[] = '';
		$cipher = '';

		$pwd_length = strlen($pwd);
		$data_length = strlen($data);

		for ($i = 0; $i < 256; $i++)
		{
			$key[$i] = ord($pwd[$i % $pwd_length]);
			$box[$i] = $i;
		}
		for ($j = $i = 0; $i < 256; $i++)
		{
			$j = ($j + $box[$i] + $key[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
		for ($a = $j = $i = 0; $i < $data_length; $i++)
		{
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
	function decrypt ($pwd, $data, $ispwdHex = 0)
	{
		return rc4crypt::encrypt($pwd, $data, $ispwdHex);
	}
}
// ======================================================================================
// END RC4 ENCRYPTION CLASS
// Do not modify.
// ======================================================================================

?>