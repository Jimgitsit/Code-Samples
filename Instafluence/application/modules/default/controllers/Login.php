<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once('BaseDefaultController.php');
require_once(APPPATH . 'third_party/SendMail_SMTP.php');

class Login extends BaseDefaultController
{

	public function init()
	{
		$this->forceSSL();
		
		if ($this->checkAuth()) {
			$account_type = $this->session->userdata('account_type');
			redirect($this->config->item('accounts')[$account_type]['url']);
		}
	}

	public function index()
	{
		if ($this->input->post('reset_password') == true) {
			$response = array('success' => true);
			$this->load->model('user_model');
			$token = $this->user_model->setUserPwResetToken($this->input->post('email'));
			if ($token != null) {
				$this->sendPwResetEmail($this->input->post('email'), $token);
				
				$response['email'] = $this->input->post('email');
			}
			else {
				$response['success'] = false;
			}
			
			echo(json_encode($response));
			exit();
		}
		else {
			//$this->processForm('login', function() {
			if ($this->input->post() != null) {
				$this->load->library('Authenticate');
				$login = $this->authenticate->login($this->input->post());
	
				//var_dump($login['redirect_url']);
				if (is_array($login)) {
					redirect($login['redirect_url']);
				}
				else {
					$this->smarty->assign('authError', 'Invalid Email or Password!');
				}
			}
			//}, true);
		}
		
		$this->smarty->assign('post', $this->input->post());
		$this->smarty->display('login/index.html');
	}
	
	private function sendPwResetEmail($toEmail, $token) {
		$email = "Please click the following link to reset your password for Instafluence.\n\n";
		$email .= $this->config->base_url() . "setpassword/?t=$token";

		$emailHtml = str_replace("\n", "<br/>", $email);

		$from = "support@instafluence.com";
		$fromName = "Instafluence";

		$mailer = new SendMail_SMTP();
		$mailer->sendEmail($toEmail, '', "Instafluence - Password Reset", $emailHtml, $email, $from, $fromName);
	}
}