<?php

namespace NAHDS;

use Zend\Ldap\Ldap;

class NAHLDAP {
	// TODO: Move these into site config
	private $ldapOptions = array(
		'host' => LDAP_HOST,
		'port' => LDAP_PORT,
		'baseDn' => LDAP_BASE_DN,
		'username' => LDAP_USERNAME,
		'password' => LDAP_PASSWORD
	);
	
	private $attrs = array(
		"givenname",
		"sn",
		"mail",
		"telephonenumber",
		"title",
		"samaccountname"
	);
	
	private $conn;
	
	public function __construct() {
		$this->conn = new Ldap($this->ldapOptions);
		// TODO: Check conn
	}
	
	public function findByName($lastName, $firstName) {
		$results = $this->conn->search("sn=$lastName,givenname=$firstName", null, Ldap::SEARCH_SCOPE_SUB, $this->attrs);
		if (iterator_count($results) != 0) {
			$results = iterator_to_array($results);
		}
		else {
			$results = array();
		}
		
		return $results;
	}
	
	public function findByUserName($userName) {
		$user = $this->conn->search("samaccountname=$userName", null, Ldap::SEARCH_SCOPE_SUB, $this->attrs);
		if (iterator_count($user) != 0) {
			$user = iterator_to_array($user)[0];
			$this->parsePhoneNumber($user);
		}
		else {
			$user = null;
		}
		
		return $user;
	}
	
	private function parsePhoneNumber(&$user) {
		$phoneNumbers = array();
		if (isset($user['telephonenumber'])) {
			// Validate and normalize phone numbers
			foreach ($user['telephonenumber'] as $ni => $number) {
				
				$phoneNumbers[$ni] = array();
				
				// Handle extensions in the phone number
				$ext = '';
				if (strstr($number, '|')) {
					$numbers = explode('|', $number, 2);
					
					$number = trim($numbers[0]);
					$ext = trim($numbers[1]);
				}
				
				// Remove all chars in the phone number except digits
				// Also remove leading 1 if there is one
				$number = preg_replace("/[^0-9,.]/", "", $number);
				if (substr($number, 0, 1) == '1') {
					$number = substr($number, 1);
				}
				
				if (!empty($number)) {
					$phoneNumbers[$ni]['number'] = $number;
					$phoneNumbers[$ni]['ext'] = $ext;
				}
				else {
					unset($phoneNumbers[$ni]);
				}
			}
		}
		
		$user['phonenumbers'] = $phoneNumbers;
	}
}