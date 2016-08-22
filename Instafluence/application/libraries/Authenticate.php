<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Authentication to provide a secure user login system
 * including password cryptography and session management
 *
 * @author Jason Maurer <jason@builtbyhq.com>
 * @copyright (c) 2014
 */

class Authenticate
{

	/** @var object */
	protected $ci;

	public function __construct()
	{
		$this->ci =& get_instance();

		$this->ci->load->model('user_model');
		$this->ci->load->library('Cryptography');
	}

	/**
	 * One way hash function using sha256 with an optional salt.
	 * Also uses CI encryption key defined in config/config.php
	 *
	 * @return array (hash, salt)
	 */
	private function _hashPassword($password, $salt = null)
	{
		$hash = $this->ci->cryptography->hash(
			$password,
			$this->ci->config->item('encryption_key'),
			$salt
		);

		return $hash;
	}

	/**
	 * Once user's credentials have been authorized, create a session
	 * with account information (log them in).
	 *
	 * @param object
	 * @return void
	 */
	private function _createSession($user)
	{
		$this->ci->load->library('session');

		$data = array(
			'auth' => true,
			'email' => $user['email'],
			'name' => $user['first_name'].' '.$user['last_name'],
			'account_type' => $user['account_type']
		);

		foreach ($data as $key => $value)
			$this->ci->session->set_userdata($key, $value);
	}

	/**
	 * Get error message format in case user is not logged in, run all
	 * methods to authenticate user and create session.
	 *
	 * @param array, bool
	 * @return bool or string
	 */
	public function login($creds, $hash = true)
	{
		/** Validate email and password **/
		$user = $this->ci->user_model->getUser($creds['email']);
		if (empty($user)) return false;

		if ($hash)
			$hash = $this->_hashPassword(trim($creds['password']), $user['salt']);
		else
			$hash['hash'] = $creds['password'];

		if ($hash['hash'] !== $user['password']) return false;

		$this->_createSession($user);
		
		if ($user['account_type'] == 1) {
			$this->ci->load->model('social_model');
			$this->ci->social_model->setUser($user['email']);
	
			// Set correct URL to redirect to after user is authenticated based on account type
			if ($this->ci->social_model->getConnectedCount() > 0) {
				$redirect_url = $this->ci->config->item('accounts')[$user['account_type']]['url'];
			}
			else {
				$redirect_url = $this->ci->config->item('accounts')[$user['account_type']]['url2'];
			}
		}
		else {
			$redirect_url = $this->ci->config->item('accounts')[$user['account_type']]['url'];
		}

		return array('auth' => true, 'redirect_url' => $redirect_url);
	}

	/**
	 * Destroy user session to logout of system
	 */
	public function logout()
	{
		$this->ci->load->library('session');
		$this->ci->session->sess_destroy();

		return true;
	}

}