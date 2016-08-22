<?php


//$ldapconn = ldap_connect('ldap://ldap.nah1.com:3890');

chdir(dirname($_SERVER['SCRIPT_FILENAME']));
require_once('../../vendor/autoload.php');

use NAHDS\NAHLDAP;

$nahldap = new NAHLDAP();
$results = $nahldap->findByUserName("md1044");
//$results = $nahldap->findByName("CROSS", "IVAN");
var_dump($results);
$stop = true;

/*
$options = array(
	'host' => 'localhost',
	'port' => '3890',
	'baseDn' => 'dc=nah1,dc=com',
	//'username' => 'pd34783@nah1.com',
	//'password' => '#Blu3Tr33$207'
	'username' => 'svc_nahlinkldap',
	'password' => '\LrP!,rd23NeH1kp\jig'
);
$ldap = new Zend\Ldap\Ldap($options);
*/
//$acctname = $ldap->getCanonicalAccountName('pd34783', Zend\Ldap\Ldap::ACCTNAME_FORM_DN);

/*
//$conn = $ldap->connect('localhost', '3890', $useSsl = false, $useStartTls = false);
//$ldap->bind();
//$ldap->bind('svc_nahlinkldap', '\LrP!,rd23NeH1kp\jig');
$result = $ldap->search("samaccountname=JW36661", null, Zend\Ldap\Ldap::SEARCH_SCOPE_SUB, array("givenname","sn","email","telephonenumber","phone","title","samaccountname"));
//$result = $ldap->search("cn=JW36661");
//$result = $ldap->search("samaccountname=JW36661");
foreach ($result as $item) {
	$stop = true;
}


//$conn->disconnect();

$stop = true;
*/

/*
$ad = new ActiveDirectory\ActiveDirectory();

//Load AD server settings from ini file
$ad->loadConfig('ldap_config.ini');

//Identify user. Uses Apache authentication (mod_auth_kerb) as primary authentication method but has http auth as fallback method.
//$login = $ad->identify();
$login = 'jim';

//Get dname for user $login
$dname = $ad->getDname($login);

//Get user information
$userInfo = $ad->getInfo($dname);

echo($userInfo);
*/

/*
//Check if user is member of an AD group (recursive search)
if($ad->isMemberOf($dname, "Test Group", true)) {
	$isMember = true;
}
else {
	$isMember = false;
}
*/