<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once('BaseDefaultController.php');
require_once(APPPATH . 'third_party/SendMail_SMTP.php');
require_once(APPPATH . 'third_party/Unirest/Unirest.php');

class Home extends BaseDefaultController
{

	public function __construct()
	{
		parent::__construct();
		
		if ($this->checkAuth())
			$this->smarty->assign('auth', true);
	}

	public function index()
	{
		$this->smarty->display('home/index.html');
	}

	public function app()
	{
		$this->smarty->display('home/app.html');
	}

	public function brand()
	{
		$this->smarty->display('home/brand.html');
	}

	public function influencer()
	{
		$this->smarty->display('home/influencer.html');
	}

	public function about()
	{
		$this->smarty->display('home/about.html');
	}

	public function portfolio()
	{
		$this->smarty->display('home/portfolio.html');
	}

	public function press()
	{
		$this->smarty->display('home/press.html');
	}
	
	/**
	 * This will be called for both the brand-form and the influencer-form
	 */
	public function contactformsubmit() {
		$post = $this->input->post();
		
		// For the hidden captcha
		if(trim($post['email_confirmation']) != "" || (trim($post[ 'email' ]) == "" && trim($post[ 'company' ]) == "0")) {
			log_message('info', 'Caught a spam attempt in contactformsubmit');
			header('Location: /confirmation');
			exit();
		}
		
		// Send email
		$this->sendContactEmail($post);
		
		// Post to Base CRM
		if(ENVIRONMENT == "production") {
			$this->sendLeadToBaseCRM($post);
			//$this->load->library('BaseCRM');
			//$this->BaseCRM->sendLeadToBaseCRM($post);
		}
		
		header('Location: /confirmation');
		exit();
	}

	public function confirmation()
	{
		$this->smarty->display('home/confirmation.html');
	}
	
	private function sendContactEmail($post) {
		if(ENVIRONMENT == "production") {
			$to = array(
				"scott@instafluence.com",
				"heather@instafluence.com",
				"nykelle@instafluence.com",
				"michael.michie@instafluence.com"
			);
		}
		else {
			$to = array(
				"jason@builtbyhq.com"
			);
		}

		$subject = "Instafluence - App or Brand Contact";

		$email  = "First Name: {$post[ 'first_name' ]}\n";
		$email .= "Last Name: {$post[ 'last_name' ]}\n";
		$email .= "Email: {$post[ 'email' ]}\n";
		$email .= "Phone: {$post[ 'phone' ]}\n";
		$email .= "Company: {$post[ 'company' ]}\n";
		$email .= "Comments: {$post[ 'comments' ]}\n";

		$emailHtml = str_replace("\n", "<br/>", $email);

		$from = "support@instafluence.com";
		$fromName = "Instafluence";

		$mailer = new SendMail_SMTP();
		$mailer->sendEmail($to, '', $subject, $emailHtml, $email, $from, $fromName);
	}

	// TODO: This should be in BaseCRM but loading the library is failing for some reason.
	private function sendLeadToBaseCRM($post) {
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
					'phone'         => $post['phone'],
					'company_name' 	=> $post['company'],
					'description' 	=> $post['comments']
				)
			);

			Unirest::verifyPeer(false);
			Unirest::post($url, array('X-Futuresimple-Token' => $token), $fields);
		}
	}
	
	/*
	private function sendToSalesforce() {
		$post = $this->input->post();
		if(!empty($post)) {
			$url = "https://www.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8";
			
			$fields = array(
				'oid' => '00DG0000000iGWQ',
				'retURL' => 'http://instafluence.local/confirmation/',
				'first_name' => $post['first_name'],
				'last_name' => $post['last_name'],
				'email' => $post['email'],
				'company' => $post['company'],
				'00NG00000067F5Z' => $post['00NG00000067F5Z']
			);
			
			$postStr = http_build_query($fields);
			
			//open connection
			$ch = curl_init();
			
			//set the url, number of POST vars, POST data
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, count($fields));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postStr);
			
			//execute post
			$result = curl_exec($ch);
			
			//close connection
			curl_close($ch);
		}
	}
	*/
}







