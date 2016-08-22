<?php

use \Gozer\Core\CoreController;
use \MongoDocs\User;

class BasePage extends CoreController {
	
	/**
	 * @var \Doctrine\ODM\MongoDB\DocumentManager
	 */
	protected $dm;
	
	/**
	 * @var User
	 */
	protected $curUser;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		try {
			session_start();
			parent::__construct();
			$this->dm = AlaCarteDB::connect();
			
			if (empty($_SESSION['user']) && !empty($_COOKIE['alacarteId'])) {
				$user = $this->dm->getRepository('MongoDocs\User')->findOneByCookieId($_COOKIE['alacarteId']);
				if ($user !== null) {
					$user->saveToSession();
				}
			}
			
			if (!$this->checkUserLevel()) {
				$meta = parse_url($_SERVER['REQUEST_URI']);
				if (!empty($meta['query'])) {
					parse_str($meta['query'], $meta['query']);
				}
				if ($meta['path'] != '/login') {
					$this->logout();
				}
			}
		}
		catch (Exception $e) {
			
		}
	}
	
	protected function handlePost() {
		if (isset($this->post['action'])) {
			switch ($this->post['action']) {
				case 'changePassword':
					$this->changePassword();
					break;
			}
		}
	}
	
	private function changePassword() {
		$this->curUser->setTempPassword(null);
		$this->curUser->setPassword($this->post['password']);
		$this->dm->persist($this->curUser);
		$this->dm->flush();
		
		$this->redirectFromPost();
	}
	
	/**
	 * Checks that the user is saved in the session is a valid user, is enabled, and is and admin.
	 * 
	 * @return bool
	 */
	public function checkUserLevel() {
		if (empty($_SESSION['user']) || empty($_SESSION['user']['id'])) {
			return false;
		}
		
		// Make sure they are still enabled
		$this->curUser = $this->dm->getRepository('MongoDocs\User')->findOneById($_SESSION['user']['id']);
		$this->curUser = User::fixProps($this->dm, $this->curUser, true);
		if ($this->curUser->getStatus() !== MongoDocs\User::STATUS_ENABLED 
			|| ($this->curUser->getLevel() !== User::USER_LEVEL_GROUP_ADMIN && $this->curUser->getLevel() !== User::USER_LEVEL_GLOBAL_ADMIN)) 
		{
			return false;
		}
		
		return true;
	}
	
	public function logOut() {
		if (!empty($_SESSION['user']) || !empty($_SESSION['user']['id'])) {
			$user = $this->dm->getRepository('MongoDocs\User')->findOneById($_SESSION['user']['id']);
			$user->removeCookieId();
			$this->dm->persist($user);
			$this->dm->flush();

			$this->setCurUser(null);

			unset($_SESSION['user']);
		}
		
		if (isset($_COOKIE['alacarteId'])) {
			unset($_COOKIE['alacarteId']);
			setcookie('alacarteId', '', time() - 3600, '/', $_SERVER['HTTP_HOST'], false, true);
		}
		header("Location: login");
		exit();
	}
	
	public function setCurUser($user) {
		$this->curUser = $user;
	}
	
	public function getCurUser() {
		return $this->curUser;
	}
	
	protected function renderTwig($template, $vars) {
		$vars['alerts'] = $this->getAlerts();
		
		if (!empty($this->post)) {
			$vars['post'] = $this->post;
		}
		else if (!empty($_SESSION['post'])) {
			$vars['post'] = $_SESSION['post'];
		}

		if (!empty($this->get)) {
			$vars['get'] = $this->get;
		}
		else if (!empty($_SESSION['get'])) {
			$vars['get'] = $_SESSION['get'];
		}
		
		if (!empty($_SESSION['user'])) {
			$vars['curUser'] = $this->curUser;
		}
		
		echo($this->twig->render($template, $vars));
		
		$this->resetAlerts();
		unset($_SESSION['post']);
		unset($_SESSION['get']);
	}

	/**
	 * Alert types are success, warning, info, and danger.
	 * 
	 * @param $type
	 * @param $text
	 */
	protected function setAlert($type, $text) {
		if (!isset($_SESSION['alerts'])) {
			$_SESSION['alerts'] = array();
		}
		$_SESSION['alerts'][] = array('type' => $type, 'text' => $text);
	}
	
	protected function getAlerts() {
		if (!empty($_SESSION['alerts'])) {
			return $_SESSION['alerts'];
		}
		return null;
	}
	
	protected function resetAlerts() {
		unset($_SESSION['alerts']);
	}

	/**
	 * After handling a post, use this method to redirect to the same (or another) page 
	 * to prevent the user from re-posting if they refresh the browser.
	 * 
	 * Post data is saved in the session in case it is needed again after redirect (for 
	 * form values for example).
	 * 
	 * @param $url
	 */
	protected function redirectFromPost($url = null) {
		if ($url == null) {
			$url = $_SERVER['REQUEST_URI'];
		}
		$_SESSION['post'] = $this->post;
		$_SESSION['get'] = $this->get;
		header("Location: $url");
		exit;
	}
}