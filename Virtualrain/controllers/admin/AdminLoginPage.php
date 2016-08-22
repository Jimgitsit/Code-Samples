<?php

require_once('AdminPage.php');
require_once('VirtualRainDB.php');

class AdminLoginPage extends AdminPage
{
	public function __construct($template)
	{
		if (isset($_POST['action']) && $_POST['action'] == "log_out") {
			session_start();
			session_destroy();
			$_SESSION = array();
		}
		parent::__construct($template, 'Login');

		$this->smarty->assign('msg', '');
		$this->smarty->assign('signup', false);

		// Check for password reset or signup
		$db = new VirtualRainDB();
		if (isset($_GET['t'])) {
			$dist = $db->getDistributerFromToken($_GET['t']);
			if ($dist == null) {
				// Redirect to login page (without query string, so normal login)
				header('Location:login');
				exit();
			}

			$this->smarty->assign('signup', true);
			$this->smarty->assign('dist', $dist);
		}
		else {
			$this->handlePost();

			if ($this->alreadyLoggedIn()) {
				$this->doPostLoginRedirect();
			}
		}
	}

	protected function alreadyLoggedIn()
	{
		return (isset($_SESSION['admin_user']) || isset($_SESSION['distributer']));
	}

	protected function handlePost()
	{
		if (!empty($_POST) && isset($_POST['action'])) {
			switch ($_POST['action']) {
				case 'admin_login': {
					//session_destroy();
					unset($_SESSION['distributer']);
					unset($_SESSION['admin_user']);

					$db = new VirtualRainDB();
					$user = $db->checkAdminLogin($_POST['email'], $_POST['pass']);
					if ($user !== false) {
						// Save the client time zone
						if (!empty($_POST['client_timezone'])) {
							$_SESSION['client_timezone'] = $_POST['client_timezone'];
						}
						else {
							// If we can't determine the client's time zone then use the server's
							$_SESSION['client_timezone'] = ini_get('date.timezone');
						}
						$_SESSION['friendly_timezone'] = str_replace('America/', '', $_SESSION['client_timezone']);
						$_SESSION['friendly_timezone'] = str_replace('_', ' ', $_SESSION['friendly_timezone']);

						// Check to see if the user is an admin or distributer
						// TODO: Check if the user is a branch manager
						if (isset($user['company_name'])) {
							// Check to see if the distributer is active
							if ($user['status'] == 0) {
								$this->smarty->assign('msg', 'Your account is disabled. Please contact the site admin.');
								break;
							}
							else {
								$_SESSION['distributer'] = $user;
							}
						}
						else {
							// Save the user in the session
							$_SESSION['admin_user'] = $user;
						}
						$this->doPostLoginRedirect();
					}
					else {
						// Invalid login
						$this->smarty->assign('msg', 'Invalid email or password.');
					}

					break;
				}
				case 'log_out': {
					session_destroy();
					break;
				}
				case 'signup': {
					//session_destroy();

					$pw1 = trim($_POST['pass1']);
					$pw2 = trim($_POST['pass2']);

					if (($pw1 == '' || $pw2 == '') || $pw1 != $pw2) {
						$this->smarty->assign('msg', 'Empty password or passwords do not match.');
					}

					if (strlen($pw1) < 8) {
						$this->smarty->assign('msg', 'Your password must be at least 8 characters in length.');
					}

					$db = new VirtualRainDB();
					$success = $db->setDistributerPassword($_POST['id'], $pw1);
					if ($success) {
						$dist = $db->getDistributer($_POST['id']);
						$_SESSION['distributer'] = $dist;
						$this->doPostLoginRedirect();
					}

					break;
				}
			}
		}
	}

	private function doPostLoginRedirect()
	{
		// Redirect according to the admin user type
		if (isset($_SESSION['admin_user'])) {
			// Admin users go here
			$this->redirect('distributers');
		}
		else {
			// Distributers go here
			$this->redirect('orders');
		}
	}
}