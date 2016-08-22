<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once('BaseDefaultController.php');

class Signup extends BaseDefaultController
{

	public function init()
	{
		$this->forceSSL();
		
		if ($this->checkAuth()) {
			redirect('/profile/');
		}

		$this->load->model('user_model');
	}

	/**
	 * Form validator callback for checking whether or
	 * not an email already exists in the database.
	 */
	public function unique_email($email)
	{
		$user = $this->user_model->getUser($email);

		if ($user != null) {
			return false;
		}
		else {
			return true;
		}
	}

	public function index()
	{
		$this->processForm('signup', function($data) {
			$this->user_model->create($data, 'influencer');

			// Login after account is created 
			$this->load->library('Authenticate');
			$creds['email'] = $data['email'];
			$creds['password'] = $data['password'];
			$this->authenticate->login($creds);
			
			redirect('/profile/');
			exit();
		}, true);

		$this->smarty->assign('post', $this->input->post());
		$this->smarty->display('signup/index.html');
	}

}