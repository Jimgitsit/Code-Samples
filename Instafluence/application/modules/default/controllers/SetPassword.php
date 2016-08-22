<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once('BaseDefaultController.php');

class SetPassword extends BaseDefaultController
{

	public function init()
	{
		$this->forceSSL();
		
		$this->load->model('user_model');
	}

	public function index()
	{
		$this->processForm('setpassword', function($data) {
			// Set new password
			$this->user_model->setUserPassword($data['email'], $data['password']);
			
			// Login
			$this->load->library('Authenticate');
			$login = $this->authenticate->login($data);

			//var_dump($login['redirect_url']);
			if (is_array($login)) {
				redirect($login['redirect_url']);
			}
			else {
				redirect('/login/');
			}
		}, true);
		
		$token = $this->input->get('t');
		if ($token == null) {
			redirect('/signup/');
			exit();
		}
		
		$user = $this->user_model->getUserByToken($token);
		if ($user === null) {
			redirect('/signup/');
			exit();
		}
		
		$this->smarty->assign('token', $token);
		$this->smarty->assign('user', $user);
		$this->smarty->display('set_password/index.html');
	}
}