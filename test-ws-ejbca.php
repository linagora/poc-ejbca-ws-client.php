<?php


// Constantes ejbca : http://www.ejbca.org/ws/constant-values.html
define('TOKEN_TYPE_P12', 'P12');
define('STATUS_NEW', 10);
define('REVOKATION_REASON_UNSPECIFIED', 0);
define('MATCH_TYPE_EQUALS', 0);
define('MATCH_WITH_USERNAME', 0);

/* parametres du script */
$url="https://dca-laptop:8443/ejbca/ejbcaws/ejbcaws?wsdl";
$url="https://ctyv0083:8443/ejbca/ejbcaws/ejbcaws?wsdl";
$url="wsdl.lina.xml";
$url="wsdl.xml";
define('CERT', 'ws-login.pem');
$passphrase='password';


/* classes pour communication WS */
class userMatch
{
    public $matchtype;
    public $matchvalue;
    public $matchwith;
}

class extendedInformationWS
{
    public $name;
    public $value;
}

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

$classmap = array(
    'userDataVows' => 'userDataVows',
    'userMatch' => 'userMatch',
    'extendedInformationWS' => 'extendedInformationWS'
);

class WrappedSoapClient extends SoapClient {
  protected function callCurl($url, $data, $action) {
     $handle   = curl_init($url);
     curl_setopt($handle, CURLOPT_URL, $url);
     curl_setopt($handle, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml", 'SOAPAction: "' . $action . '"'));
     curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
     curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
     curl_setopt($handle, CURLOPT_SSLVERSION, 3);
     curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, FALSE);
     curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, FALSE);
     curl_setopt($handle, CURLOPT_SSLCERT, CERT);
     $response = curl_exec($handle);
     if (empty($response)) {
       throw new SoapFault('CURL error: '.curl_error($handle),curl_errno($handle));
     }
     curl_close($handle);
     return $response;
   }

   public function __doRequest($request,$location,$action,$version,$one_way = 0) {
       return $this->callCurl($location, $request, $action);
   }
 }


$client=new SoapClient(
	$url,
	array(
		'trace' => 1,
		'local_cert' => CERT,
		'passphrase' => $passphrase,
		'classmap' => $classmap));


function dummy_User($name) {
    $extendedInformation = new extendedInformationWS();
    $extendedInformation->name="subjectdirattributes";
    $extendedInformation->value="";

    $userData=new userDataVOWS();
    $userData->caName = "CA_TEST_LAPOSTE";
    $userData->caName = "ManagementCA";
    /* $userData->cardNumber=null; */
    $userData->certificateProfileName = "CP_USER-AUTH";
    /* $userData->certificateSerialNumber = NULL; */
    $userData->clearPwd = FALSE;
    $userData->email = $name . "@laposte.fr";
    $userData->endEntityProfileName = "EP_USER-AUTH";
    /* $userData->endTime=null; */
    $userData->extendedInformation=$extendedInformation;
    /* $userData->hardTokenIssuerName=null; */
    $userData->keyRecoverable=FALSE;
    $userData->password = "password";
    $userData->sendNotification=FALSE;
    /* $userData->startTime=null; */
    $userData->status = STATUS_NEW;
    $userData->subjectAltName="";
    $userData->subjectDN = "CN=TEST,O=LA POSTE,C=FR";
    $userData->subjectDN = "CN=test,OU=AUTH,O=LA POSTE,C=FR";
    $userData->tokenType = TOKEN_TYPE_P12;
    $userData->username = $name;

    return $userData;
}


function find_user($username, $client) {
    try{
        $matcher= new userMatch();
        $matcher->matchtype= MATCH_TYPE_EQUALS;
        $matcher->matchvalue = $username;
        $matcher->matchwith = MATCH_WITH_USERNAME;

        return $client->findUser(array("arg0" => $matcher));
        // var_dump($client->__getLastResponse());
    } catch(Exception $e) {
        var_dump($e);
    }
}

function editUser($userData, $client)
{
    try {
        return $client->editUser(array('arg0' => $userData));
    } catch(Exception $e) {
        var_dump($e);
        var_dump($client->__getLastRequest());
    }
}

function generateCert($username, $client) {
    try {
        return $client->pkcs12Req(array(
            'arg0' => $username,
            'arg1' => "password",
            //'arg2' => '',
            'arg3' => '2048',
            'arg4' => 'RSA'));
    } catch (Exception $e) {
        var_dump($e);
    }
}

function renew($username, $client) {
    try{
        $user = find_user($username, $client)->return;
        $user->status = STATUS_NEW;
        $user->password = "password";
        editUser($user,$client);
        return generateCert($username, $client);
    } catch (Exception $e) {
        var_dump($e);
    }
}

function revoke($username, $client) {
    try{
        var_dump($client->revokeUser(array(
            "arg0" => $username,
            "arg1" => REVOKATION_REASON_UNSPECIFIED,
            "arg2" => FALSE)));
        var_dump($client->__getLastResponse());
    } catch (Exception $e) {
        var_dump($e);
    }
}


$timestart=microtime(true);
echo "Debut du script ".date("H:i:s", $timestart). "\n";

for($i=600; $i > 100; $i--) {
    $username='test0' . $i;
    editUser(dummy_User($username), $client);
    generateCert($username, $client);
}

$createdUsers=microtime(true);
echo "500 certificats crees à  ".date("H:i:s", $createdUsers). "\n";
echo "temps de creation de 500 certificats  " . number_format($createdUsers-$timestart, 3) . "secondes\n";
echo "moyenne de temps de creation d'un certificat " . number_format(($createdUsers-$timestart)/500, 3). "secondes\n";

for($i=600; $i > 100; $i--) {
    $username='test0' . $i;
    revoke($username, $client);
}

$revokedUsers=microtime(true);
echo "500 certificats revoques à  ".date("H:i:s", $revokedUsers). "\n";
echo "temps de revocation de 500 certificats  " . number_format($revokedUsers-$createdUsers, 3) . "secondes\n";
echo "moyenne de temps de revocation d'un certificat " . number_format(($revokedUsers-$createdUsers)/500, 3). "secondes\n";

echo "Maintenat je calcule le nombre maximal de creations en une heure! CELA PRENDRA UNE HEURE!";

$total=0;
for($endTime=$revokedUsers+(60*60); microtime(true) < $endTime; $total++) {
    $username="test0" . ($total + 100);
    editUser(dummy_User($username), $client);
    generateCert($username, $client);
}

echo "nombre de certificats crées en une heure". $total. "\n";
echo "moyenne de temps de creation d'un certificat " . (60*60)/$total . "secondes\n";


?>
