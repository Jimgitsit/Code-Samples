<?php

class BaseCRM {
	
	public function sendLeadToBaseCRM($post) {
		if(!empty($post)) {
			// Authenticate with the Base CRM API and get a token
			$url = "https://sales.futuresimple.com/api/v1/authentication";
			$fields = array(
				'email' 	=> 'aaron@instafluence.com',
				'password' 	=> '14themoney'
			);

			Unirest::clearDefaultHeaders();
			Unirest::verifyPeer(false);
			$result = Unirest::post($url, null, $fields);
			if(!isset($result->body->authentication->token)) {
				// Authentication failed
				return;
			}

			$token = $result->body->authentication->token;

			// Create a new lead
			$url = "https://leads.futuresimple.com/api/v1/leads.json";
			$fields = array(
				'lead' => array(
					'first_name' 	=> $post['first_name'],
					'last_name' 	=> $post['last_name'],
					'email' 		=> $post['email'],
					//'company_name' 	=> $post['company'],
					//'description' 	=> $post['00NG00000067F5Z']
				)
			);

			Unirest::verifyPeer(false);
			Unirest::post($url, array('X-Futuresimple-Token' => $token), $fields);
		}
	}
} 