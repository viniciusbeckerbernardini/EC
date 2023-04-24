<?php
require_once('vendor/autoload.php');
use Vinicius\Ecc\Ecies;
use Elliptic\EC;

$ec = new EC('secp256k1');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
	if($_SESSION['privateKey'] === null){
		$_SESSION['privateKey'] = $ec->genKeyPair();
		$_SESSION['publicKey'] = $ec->keyFromPublic($_SESSION['privateKey']->getPublic());
	}
}

var_dump('<pre>',$_SESSION);
// Generate private key
$privateKey = $_SESSION['privateKey'];
// Get public key
$publicKey = $_SESSION['publicKey'];
// Input to encrypt
$text = $_POST['text'];
$option = $_POST['option'];

$ecies = new Ecies($privateKey, $publicKey);

$cipher = $ecies->encrypt($text);
if($option == 1){
	$result = bin2hex($cipher);
}else{
	$decryptedText = $ecies->decrypt(hex2bin($text));
	$result = $decryptedText;
}


require './html/form.php';