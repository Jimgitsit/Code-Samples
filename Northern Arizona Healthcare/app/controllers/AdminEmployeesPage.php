<?php

use NAHDS\NAHLDAP;
use Zend\Ldap\Exception\LdapException;

class AdminEmployeesPage extends BasePageController {
	
	public function defaultAction() {
		
		$this->handleAjax();
		$this->handleGet();
		
		$em = $this->getEntityManager();
		$this->twigVars['properties'] = EntityBase::selectDistinct($em, 'Employee', 'property');
		$this->twigVars['jobClasses'] = EntityBase::selectDistinct($em, 'Employee', 'jobClass');
		
		$this->twig->display('admin-employees.twig', $this->twigVars);
	}
	
	private function handleGet() {
		if (!empty($this->get)) {
			$this->twigVars['urlParams'] = $this->get;
		}
		
		$orderBy = "e.lastName";
		$orderDir = "ASC";
		if (!empty($this->get['sort'])) {
			$parts = explode(':', $this->get['sort']);
			$orderBy = 'e.' . $parts[0];
			
			// Exception for isActive since it is a derived field
			if ($parts[0] == 'isActive') {
				$orderBy = $parts[0];
			}
			
			$orderDir = strtoupper($parts[1]);
		}
		
		if (!empty($this->get['search'])) {
			$api = new NAHDirectoryService(true);
			//$this->get['search'], 100, array('*'), true, false
			$results = $api->searchEmployees(array(
				'search-phrase' => $this->get['search'],
				'max-results' => 100,
				'include-inactive' => true,
				'only-inactive' => false
			));
			$this->twigVars['employees'] = $results;
		}
		else {
			$maxResults = 100;
			if (!empty($this->get['max'])) {
				if ($this->get['max'] !== 'all') {
					$maxResults = $this->get['max'];
				}
				else {
					$maxResults = 100000;
				}
			}
			
			$em = $this->getEntityManager();
			$conn = $em->getConnection();
			
			$sql = 'SELECT e.*, IF(e.terminationDate IS NULL OR e.terminationDate > NOW(), true, false) AS isActive FROM employees e';
			$sql .= " ORDER BY $orderBy $orderDir";
			$sql .= " LIMIT $maxResults";
			
			$stmt = $conn->prepare($sql);
			$stmt->execute();
			
			$this->twigVars['employees'] = $stmt->fetchAll();
		}
	}
	
	protected function handleAjax() {
		if (!empty($this->post['ajaxAction'])) {
			switch ($this->post['ajaxAction']) {
				case 'get-ad-data':
					if (!empty($this->post['userName'])) {
						try {
							$nahldap = new NAHLDAP();
							$adUser = $nahldap->findByUserName($this->post['userName']);
						}
						catch(LdapException $e) {
							echo($e->getMessage());
							exit();
						}
						if ($adUser !== null) {
							
							$response = array(
								'firstName' => '',
								'lastName' => '',
								'title' => '',
								'email' => ''
							);
							
							if (!empty($adUser['givenname'][0])) {
								$response['firstName'] = trim($adUser['givenname'][0]);
							}
							
							if (!empty($adUser['sn'][0])) {
								$response['lastName'] = trim($adUser['sn'][0]);
							}
							
							if (!empty($adUser['title'][0])) {
								$response['title'] = trim($adUser['title'][0]);
							}
							
							if (!empty($adUser['mail'][0])) {
								$response['email'] = trim($adUser['mail'][0]);
							}
							
							echo(json_encode($response));
						}
						else {
							header("HTTP/1.0 400 Bad Request");
							echo("Username {$this->post['userName']} not found in Active Directory.");
						}
					}
					break;
			}
			
			exit();
		}
	}
}