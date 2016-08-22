<?php

class AdminKeywordsPage extends BasePageController {
	
	private $type = 'employee';
	
	public function defaultAction() {
		
		$this->handleAjax();
		
		$this->handleGet();
		
		$em = $this->getEntityManager();
		
		$this->twigVars['types'] = EntityBase::selectDistinct($em, 'SearchPhrase', 'type');
		
		$orderBy = "s.phrase";
		$orderDir = "ASC";
		if (!empty($this->get['sort'])) {
			$parts = explode(':', $this->get['sort']);
			$orderBy = $parts[0];
			$orderDir = strtoupper($parts[1]);
			if ($orderBy == 'name') {
				if ($this->type == 'employee') {
					$orderBy = 'e.lastName';
				}
				else {
					$orderBy = 'b.displayName';
				}
			}
		}
		
		$conn = $em->getConnection();
		$sql = 'SELECT s.*, ';
		if ($this->type == 'employee') {
			$sql .= 'e.employeeID, e.firstName, e.lastName FROM search_phrases AS s ';
			$sql .= "LEFT JOIN employees AS e ON s.type = 'employee' AND s.typeID = e.employeeID ";
		}
		if ($this->type == 'business') {
			$sql .= 'b.businessID, b.displayName FROM search_phrases AS s ';
			$sql .= "LEFT JOIN businesses AS b ON s.type = 'business' AND s.typeID = b.businessID ";
		}
		if ($this->type == 'provider') {
			$sql .= 'p.providerNPI, p.firstName, p.lastName FROM search_phrases AS s ';
			$sql .= "LEFT JOIN providers AS p ON s.type = 'provider' AND s.typeID = p.providerNPI ";
		}
		
		$sql .= "WHERE s.type = '$this->type' ";
		$sql .= "ORDER BY $orderBy $orderDir";
		
		$stmt = $conn->prepare($sql);
		$stmt->execute();
		$this->twigVars['keywords'] = $stmt->fetchAll();
		
		$this->twigVars['types'] = MetaData::selectDistinct($em, 'SearchPhrase', 'type');
		
		$this->twig->display('admin-keywords.twig', $this->twigVars);
	}
	
	private function handleGet() {
		if (!empty($this->get)) {
			$this->twigVars['urlParams'] = $this->get;
			
			if (!empty($this->get['search'])) {
				$ds = new NAHDirectoryService(true);
				$this->twigVars['searchResults'] = $ds->search(array(
					'search-phrase' => $this->get['search'],
					'scope' => array('businesses', 'employees', 'providers'),
					'include-inactive' => true
				));
			}
			
			if (!empty($this->get['type'])) {
				$this->type = $this->get['type'];
			}
		}
	}
	
	private function handleAjax() {
		if (!empty($this->post) && !empty($this->post['action'])) {
			switch ($this->post['action']) {
				case 'get-entity':
					$entity = null;
					$em = $this->getEntityManager();
					if ($this->post['type'] == 'employee') {
						$entity = $em->getRepository('Employee')->findOneByEmployeeID($this->post['typeID']);
					}
					else if ($this->post['type'] == 'business') {
						$entity = $em->getRepository('Business')->findOneByBusinessID($this->post['typeID']);
					}
					else if ($this->post['type'] == 'provider') {
						$entity = $em->getRepository('Provider')->findOneByProviderNPI($this->post['typeID']);
					}
					echo(json_encode($entity));
					break;
			}
			
			exit();
		}
	}
}