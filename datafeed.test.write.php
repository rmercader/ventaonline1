<?php
/**
 * FoxyCart XML to File Script
 * 
 * @link http://wiki.foxycart.com/integration:misc:test_xml_post
 * @version 0.6a
 * @license http://www.gnu.org/copyleft/gpl.html
 */
/*
	DESCRIPTION: =================================================================
	The purpose of this file is to help you set up and debug your FoxyCart XML DataFeed scripts.
	It will take the XML from FoxyCart, decrypt it, and write it to a file on your server.
	READ THE WARNING BELOW. This script is FOR TESTING ONLY.
 
	WARNING: =====================================================================
	This script is for TESTING ONLY.
	It's not safe to leave your customer data sitting on your server.
	Nobody but you will be held responsible if something bad happens because you left customer data out in the open.
 
	USAGE: =======================================================================
	- Place this file somewhere on your server.
	- Edit the $myKey to match the key you put in your FoxyCart admin.
	- Set the $file to where you'd like the file written.
	- Save.
	- In the FoxyCart admin set the datafeed URL to the address for this script.
	- Test until you get your script working properly.
	- Remove this script from your server and disable (or change) the datafeed URL in the FoxyCart admin.
 
	REQUIREMENTS: ================================================================
	- PHP
*/
 
// ======================================================================================
// CHANGE THIS DATA:
// Set the URL you want to post the XML to.
// Set the key you entered in your FoxyCart.com admin.
// Modify the XML below as necessary.  DO NOT modify the structure, just the data
// ======================================================================================
 
$myKey = 'DAQ5QLtZ8gGkFtB5wdqGwCqUMCG7IWsgm8f7Pz1MFx3fQyehBvN325RFBpCa'; // your foxy cart datafeed key
 
// The filename that you'd like to write to.
// For security reasons, this file should either be outside of your public web root,
// or it should be written to a directory that doesn't have public access (like with an .htaccess directive).
$file = 'datafeed.archive.xml';
 
if (isset($_POST["FoxyData"]) OR isset($_POST['FoxySubscriptionData'])) {
    	$FoxyData_encrypted = (isset($_POST["FoxyData"])) ? urldecode($_POST["FoxyData"]) : urldecode($_POST["FoxySubscriptionData"]);
	$FoxyData_decrypted = rc4crypt::decrypt($myKey,$FoxyData_encrypted);
	$fh = fopen($file, 'a') or die("Couldn't open $file for writing!"); 
	fwrite($fh, $FoxyData_decrypted);
	fclose($fh);
	echo 'foxy';
} else {
	echo 'error';
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
 
?>