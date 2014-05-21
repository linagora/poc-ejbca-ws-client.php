<?php
$url="https://ctyv0083:8443/ejbca/ejbcaws/ejbcaws?wsdl";
$cert="admin-ws.pem";
$passphrase='password';

class userDataVOWS
{
	public $username;
	public $password;
	public $clearPwd;	
	public $subjectDN;
	public $caName;
	public $email;
	public $status;
	public $tokenType;
	public $endEntityProfileName;
	public $certificateProfileName;	
	public $certificateSerialNumber;
	public $cardNumber;
	public $endTime;
	public $extendedInformation;
	public $hardTokenIssuerName;
	public $keyRecoverable;
	public $sendNotification;
	public $startTime;
	public $subjectAltName;
}

$classmap = array('userDataVows' => 'userDataVows');
$TOKEN_TYPE_P12="P12";
$STATUS_NEW=10;

$client=new SoapClient(
	$url,
	array(
		'trace' => 1,
		'local_cert' => $cert,
		'passphrase' => $passphrase,
		'classmap' => $classmap));

$userData=new userDataVOWS();
$userData->username = "test0001";
$userData->password = "password";
$userData->clearPwd = FALSE;
$userData->subjectDN = "CN=TEST,O=LA POSTE,C=FR";
$userData->caName = "ManagementCA";
$userData->email = "test0001@laposte.fr";
$userData->status = $STATUS_NEW;
$userData->tokenType = $TOKEN_TYPE_P12;
$userData->endEntityProfileName = "EP_USER-AUTH";
$userData->certificateProfileName = "CP_USER-AUTH";
$userData->cardNumber=null;
$userData->endTime=null;
$userdata->extendedInformation=null;
$userData->hardTokenIssuerName=null;
$userData->keyRecoverable=null;
$userData->sendNotification=null;
$userdata->startTime=null;
$userdata->subjectAltName=null;

#print_r($client->revokeUser(array("test")));
#print_r($client->__getFunctions());
#var_dump($client->editUser($userData));
#print_r($client->pkcs12Req(array("test", "testPassword", "", "1024", "RSA")));
#print_r($client->revokeCert(array("test", "testPassword")));
#renouveller avec editUser a nouveau (status) et refaire pkcs12

try{
print_r($client->findUser("test01"));
} catch(Exception $e) {
var_dump($client->__getLastRequest());
}

