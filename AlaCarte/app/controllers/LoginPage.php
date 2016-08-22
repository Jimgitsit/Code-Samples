<?php

use MongoDocs\User;

class LoginPage extends BasePage {

	private $vars = array();
	
	public function init() {
		$this->initTwig();
		
		$this->vars = array('pageTitle' => 'Login');
		
		$this->handlePost();
		
		if (!empty($_SESSION['user'])) {
			header("Location: users");
			exit();
		}
		
		if ($this->checkUserLevel()) {
			$this->setCurUser($_SESSION['user']);
			$this->redirectFromPost('/user');
		}
		
		$this->renderTwig('login.twig', $this->vars);
	}
	
	protected function handlePost() {
		parent::handlePost();
		
		if (isset($this->post['action'])) {
			switch ($this->post['action']) {
				case 'login':
					$this->login($this->post);
					break;
			}
		}
	}
	
	private function login($params) {
		if (empty($params['email']) || empty($params['password'])) {
			$this->vars['msg'] = "Please enter your email and password";
			return;
		}
		
		$user = $this->dm->getRepository('MongoDocs\User')->findOneByEmail($params['email']);
		if ($user !== null) {
			$user->saveToSession();
			if (!$this->checkUserLevel() || (!$user->verifyPassword($params['password']) && !$user->verifyTempPassword($params['password']))) {
				$user->removeFromSession();
				$this->vars['msg'] = "Invalid email or password";
				return;
			}
		}
		else {
			$this->vars['msg'] = "Invalid email or password";
			return;
		}
		
		if (!empty($params['remember'])) {
			$user->setCookieId();
			$this->dm->persist($user);
			$this->dm->flush();
			
			$exp = mktime() . (time() + 60 * 60 * 24 * 30);
			// TODO: Set 6th param to true when on https
			setcookie('alacarteId', $user->getCookieId(), $exp, '/', $_SERVER['HTTP_HOST'], false, true);
		}
		
		$this->setCurUser($user);
		
		$this->redirectFromPost('/users');
	}
}