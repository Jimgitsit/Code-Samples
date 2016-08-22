<?php

use MongoDocs\TriggerDef;
use MongoDocs\Group;
use MongoDocs\User;

class ApiAdminPage extends BasePage {
	
	private $vars = array();
	
	public function init() {
		$this->initTwig();
		$this->vars = array('pageTitle' => 'API Management');
		
		$this->handlePost();
		
		$qb = $this->dm->createQueryBuilder('MongoDocs\Group');
		if ($this->curUser->getLevel() == User::USER_LEVEL_GROUP_ADMIN) {
			$qb->field('machineName')->equals($this->curUser->getGroup()->getMachineName());
		}
		$qb->sort('machineName', 'asc');
		$groups = $qb->getQuery()->execute();
		$this->vars['groups'] = $groups;
		
		$this->initTriggerDefs($this->get);
		
		$this->renderTwig('apiadmin.twig', $this->vars);
	}

	protected function handlePost() {
		parent::handlePost();
		
		if (isset($this->post['action'])) {
			switch ($this->post['action']) {
				case 'saveTriggerDef':
					$this->saveTriggerDef($this->post);
					break;
				case 'deleteTriggerDefAjax':
					$this->deleteTriggerDefAjax($this->post['id']);
					break;
				case 'getTriggerDefAjax':
					$this->getTriggerDefAjax($this->post['id']);
					break;
			}
		}
	}
	
	private function initTriggerDefs($filters) {
		$qb = $this->dm->createQueryBuilder('MongoDocs\TriggerDef');
		
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

		$triggerDefs = $qb->getQuery()->execute();
		$this->vars['triggerDefs'] = iterator_to_array($triggerDefs);
	}
	
	private function saveTriggerDef($params) {
		$triggerDef = null;
		
		if ($params['mode'] == 'edit') {
			if (empty($params['triggerDefId'])) {
				throw new Exception("triggerDefId not defined");
			}

			$triggerDef = $this->dm->getRepository('MongoDocs\TriggerDef')->findOneById($params['triggerDefId']);
			if ($triggerDef == null) {
				throw new Exception("Trigger Def not found. ID: " . $params['triggerDefId']);
			}
		}
		else {
			$triggerDef = new TriggerDef();
		}
			
		if ($triggerDef != null) {
			$triggerDef->setName($params['triggerDefName']);
			$triggerDef->setMachineName($params['machineName']);
			$triggerDef->setType($params['triggerDefType']);
			
			$options = array();
			
			switch ($params['triggerDefType']) {
				case 'text':
					$options = array(
						'minLength' => $params['textMinLength'],
						'maxLength' => $params['textMaxLength']
					);
					break;
				case 'numeric':
					$options = array(
						'numericMin' => $params['numericMin'],
						'numericMax' => $params['numericMax']
					);
					break;
				case 'yesNo':
					$options = array(
						'yesDisplayValue' => trim($params['yesDisplayValue']),
						'yesActualValue' => trim($params['yesActualValue']),
						'noDisplayValue' => trim($params['noDisplayValue']),
						'noActualValue' => trim($params['noActualValue'])
					);
					break;
				case 'staticOpts':
					if (!empty($params['staticOptionDisplay']) && 
						!empty($params['staticOptionValue']) && 
						count($params['staticOptionDisplay']) == count($params['staticOptionValue']))
					{
						$options = array('staticOptions' => array());
						for ($i = 0; $i < count($params['staticOptionDisplay']); $i++) {
							$options['staticOptions'][] = array(
								'display' => trim($params['staticOptionDisplay'][$i]),
								'value' => trim($params['staticOptionValue'][$i])
							);
						}
					}
					
					break;
				default:
					throw new Exception("Bad trigger type");
					break;
			}
			
			$triggerDef->setOptions($options);
			
			// Unless the current user is a global admin, the group will be the same as the current user
			if ($this->curUser->getLevel() != User::USER_LEVEL_GLOBAL_ADMIN) {
				$group = $this->curUser->getGroup();
			}
			else {
				$group = $this->dm->getRepository('MongoDocs\Group')->findOneByName($params['triggerDefGroup']);
				if ($group === null) {
					$this->setAlert('danger', "Invalid group. Trigger definition not saved.");
					$this->redirectFromPost('/apiadmin');
				}
			}
			
			$triggerDef->setGroup($group);

			$this->dm->persist($triggerDef);
			$this->dm->flush();
		}

		// Redirect to avoid re-posting on refresh
		$this->redirectFromPost('/apiadmin');
	}
	
	private function deleteTriggerDefAjax($id) {
		$td = $this->dm->getRepository('MongoDocs\TriggerDef')->findOneById($id);
		$this->dm->remove($td);
		$this->dm->flush();
		$this->setAlert('info', "Trigger definition deleted.");
	}
	
	private function getTriggerDefAjax($id) {
		$td = $this->dm->getRepository('MongoDocs\TriggerDef')->findOneById($id);
		header("Content-Type: application/json");
		echo(json_encode($td));
		exit();
	}
}