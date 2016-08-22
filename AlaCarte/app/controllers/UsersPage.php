<?php

use MongoDocs\Group;
use MongoDocs\User;

class UsersPage extends BasePage {
	
	private $vars = array();
	
	public function init() {
		$this->initTwig();
		$this->vars = array('pageTitle' => 'User Management');
		
		$this->handlePost();
		
		$this->vars['userLevels'] = array();
		foreach (User::$userLevels as $userLevel) {
			$this->vars['userLevels'][] = array(
				'value' => $userLevel,
				'name' => User::userLevelName($userLevel)
			);
		}

		$qb = $this->dm->createQueryBuilder('MongoDocs\Group');
		if ($this->curUser->getLevel() == User::USER_LEVEL_GROUP_ADMIN) {
			$qb->field('machineName')->equals($this->curUser->getGroup()->getMachineName());
		}
		$qb->sort('machineName', 'asc');
		$groups = $qb->getQuery()->execute();
		$this->vars['groups'] = $groups;
		
		$this->vars['users'] = $this->getUsers($this->get);
		
		$this->vars['statuses'] = User::$statusTypes;
		
		$this->renderTwig('users.twig', $this->vars);
	}
	
	protected function handlePost() {
		parent::handlePost();
		
		if (isset($this->post['action'])) {
			switch ($this->post['action']) {
				case 'saveGroup':
					$this->saveGroup($this->post);
					break;
				case 'saveUser':
					$this->saveUser($this->post);
					break;
				case 'getUserAjax':
					$this->getUserAjax($this->post['id']);
					break;
				case 'getGroupAjax':
					$this->getGroupAjax($this->post['id']);
					break;
			}
		}
	}
	
	private function getUsers($filters) {
		$qb = $this->dm->createQueryBuilder('MongoDocs\User');
		
		if ($this->curUser->getLevel() == User::USER_LEVEL_GLOBAL_ADMIN) {
			if (!empty($filters['group']) && $filters['group'] != 'all') {
				$group = $this->dm->getRepository('MongoDocs\Group')->findOneByName($filters['group']);
				if ($group != null) {
					$qb->field('group')->references($group);
				}
			}
		}
		else {
			if ($this->curUser->getGroup() !== null) {
				$qb->field('group')->references($this->curUser->getGroup());
			}
		}

		if (isset($filters['status']) && $filters['status'] != 'all') {
			$qb->field('status')->equals($filters['status']);
		}
		
		// This does not do case-insensitive sorting :(
		//$qb->sort('group');
		//$qb->sort('name');
		
		$users = $qb->getQuery()->execute();
		
		// Sort by group and user name
		$users = iterator_to_array($users);
		usort($users, function($a, $b) {
			$aGroupName = '';
			if ($a->getGroup() !== null) {
				$aGroupName = $a->getGroup()->getName();
			}
			$bGroupName = '';
			if ($b->getGroup() !== null) {
				$bGroupName = $b->getGroup()->getName();
			}
			
			if ($aGroupName == $bGroupName) {
				return strcmp(strtolower($a->getName()), strtolower($b->getName()));
			}
			return strcmp(strtolower($aGroupName), strtolower($bGroupName));
		});
		
		return $users;
	}
	
	private function getUserAjax($id) {
		$user = $this->dm->getRepository('MongoDocs\User')->findOneById($id);
		$user = User::fixProps($this->dm, $user);
		header("Content-Type: application/json");
		echo(json_encode($user));
		exit();
	}
	
	private function getGroupAjax($id) {
		$group = $this->dm->getRepository('MongoDocs\Group')->findOneById($id);
		header("Content-Type: application/json");
		echo(json_encode($group));
		exit();
	}
	
	private function saveGroup($params) {
		
		$group = null;
		
		if ($params['mode'] == 'new') {
			if (empty($params['groupName'])) {
				throw new Exception("Bad params");
			}
			
			$group = $this->dm->getRepository('MongoDocs\Group')->findOneByName(new MongoRegex("/{$params['groupName']}/i"));
			if ($group == null) {
				$group = new Group();
			}
			else {
				$this->setAlert('warning', 'Could not save group. A group with that name already exists.');
			}
		}
		else if ($params['mode'] == 'edit') {
			if (empty($params['groupId'])) {
				throw new Exception("Bad params");
			}
			
			$group = $this->dm->getRepository('MongoDocs\Group')->findOneById($params['groupId']);
			if ($group == null) {
				$this->setAlert('danger', 'Could not save group. Group with id ' . $params['groupId'] . ' could not be found.');
			}
		}
		
		if ($group != null) {
			$group->setName(trim($params['groupName']));
			$machineName = strtolower(str_replace(' ', '_', trim($params['groupName'])));
			$group->setMachineName($machineName);
			$group->setDomains($this->post['domains']);
			
			$this->dm->persist($group);
			$this->dm->flush();
		}
		
		// Redirect to avoid re-posting on refresh
		$this->redirectFromPost();
	}
	
	private function saveUser($params) {

		$user = null;
		
		if ($params['mode'] == 'new') {
			if (empty($params['userEmail'])) {
				throw new Exception("Bad params");
			}
			
			$user = $this->dm->getRepository('MongoDocs\User')->findOneByEmail(new MongoRegex("/{$params['userEmail']}/i"));
			if ($user == null) {
				$user = new User();
			}
			else {
				$this->setAlert('warning', 'Could not save user. A user with that email already exists.');
			}

			if ($user != null) {
				$user->setEmail(trim($params['userEmail']));

				// Create OAuth access
				$user->setApiClientId(trim($params['userEmail']));
				$user->setApiSecret(bin2hex(openssl_random_pseudo_bytes(22, $strong)));
				$api = new AlaCarteAPI(true);
				$api->addOAuthUser($user->getApiClientId(), $user->getApiSecret());
			}
		}
		else if ($params['mode'] == 'edit') {
			if (empty($params['userId'])) {
				throw new Exception("Bad params");
			}
			
			$user = $this->dm->getRepository('MongoDocs\User')->findOneById($params['userId']);
			if ($user == null) {
				$this->setAlert('danger', 'Could not save user. User with id ' . $params['userId'] . ' could not be found.');
			}
		}
		
		if ($user != null) {
			$user->setName(trim($params['userName']));
			$user->setStatus($params['userStatus']);

			if (trim($params['userEmail']) != '') {
				$user->setEmail(trim($params['userEmail']));
			}
			
			if (trim($params['userPassword']) != '') {
				$user->setPassword(null);
				$user->setTempPassword(trim($params['userPassword']));
			}

			$user->setLevel($params['userLevel']);

			// Global admins belong to no group
			if ($params['userLevel'] != User::USER_LEVEL_GLOBAL_ADMIN) {
				if ($user->getGroup() !== null) {
					$user->getGroup()->removeUser($user);
				}
				
				// Unless the current user is a global admin, the group will be the same as the current user
				if ($this->curUser->getLevel() != User::USER_LEVEL_GLOBAL_ADMIN) {
					$group = $this->curUser->getGroup();
				}
				else {
					$group = $this->dm->getRepository('MongoDocs\Group')->findOneByName($params['userGroup']);
					if ($group === null) {
						$this->setAlert('danger', "Invalid group. User not saved.");
						$this->redirectFromPost('/users');
					}
				}
				
				if ($group !== null) {
					$group->addUser($user);
				}
				
				$user->setGroup($group);
			}
			else {
				$user->setGroup(null);
			}

			$this->dm->persist($user);
			$this->dm->flush();
		}

		// Redirect to avoid re-posting on refresh
		$this->redirectFromPost();
	}
}