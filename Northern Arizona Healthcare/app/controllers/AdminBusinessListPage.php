<?php

class AdminBusinessListPage extends BasePageController {
	
	public function defaultAction() {
		
		if (!empty($this->post['download'])) {
			$this->outputListFile();
		}
		
		$showInactive = false;
		if (!empty($this->get['showInactive']) && $this->get['showInactive'] == true) {
			$showInactive = true;
		}
		
		$em = $this->getEntityManager();
		
		$orderBy = "displayName ASC";
		if (!empty($this->get['sort'])) {
			$parts = explode(':', $this->get['sort']);
			$orderBy = $parts[0] . ' ' . strtoupper($parts[1]);
		}
		
		// Get the parents
		$sql = "SELECT b.businessID, b.type, b.costCenter, b.propertyLocation, b.name, b.directorID, b.vpID, b.isNew, b.isActive, b.displayName, b.source, b.isBlind, b.created, b.lastUpdated, 
				(SELECT CONCAT(firstName, ' ', lastName) FROM employees WHERE employeeID = b.directorID) AS director, 
				(SELECT CONCAT(firstName, ' ', lastName) FROM employees WHERE employeeID = b.vpID) AS vp, 
				(SELECT COUNT(*) FROM employees WHERE employees.costCenter = b.costCenter) AS employeeCount, 
				(SELECT COUNT(*) FROM businesses bb WHERE bb.parentBusinessID = b.businessID) AS children FROM businesses AS b 
				WHERE b.parentBusinessID = 0";
		
		if (!$showInactive) {
			$sql .= " AND isActive = 1";
		}
		
		$sql .= " ORDER BY $orderBy";
		$stmt = $em->getConnection()->prepare($sql);
		$stmt->execute();
		$parents = $stmt->fetchAll();
		
		// Get the children
		foreach ($parents as &$parent) {
			$sql = "SELECT b.businessID, b.type, b.costCenter, b.propertyLocation, b.name, b.directorID, b.vpID, b.isNew, b.isActive, b.displayName, b.source, b.isBlind, b.created, b.lastUpdated, 
					(SELECT CONCAT(firstName, ' ', lastName) FROM employees WHERE employeeID = b.directorID) AS director, 
					(SELECT CONCAT(firstName, ' ', lastName) FROM employees WHERE employeeID = b.vpID) AS vp, 
					(SELECT COUNT(*) FROM employees WHERE employees.costCenter = b.costCenter AND (employees.terminationDate IS NULL OR employees.terminationDate >= NOW())) AS employeeCount FROM businesses AS b 
					WHERE b.parentBusinessID = {$parent['businessID']}";
			
			if (!$showInactive) {
				$sql .= " AND isActive = 1";
			}
			
			$sql .= " ORDER BY $orderBy";
			$stmt = $em->getConnection()->prepare($sql);
			$stmt->execute();
			$children = $stmt->fetchAll();
			$parent['children'] = $children;
			
			$parent['totalEmployeeCount'] = $parent['employeeCount'];
			foreach ($children as $child) {
				$parent['totalEmployeeCount'] += $child['employeeCount'];
			}
		}
		
		$this->twigVars['businesses'] = $parents;
		
		$this->twigVars['businessTypes'] = EntityBase::selectDistinct($em, 'Business', 'type');
		$this->twigVars['propertyReportsTo'] = EntityBase::selectDistinct($em, 'Business', 'propertyReportsTo');
		$this->twigVars['propertyLocations'] = EntityBase::selectDistinct($em, 'Business', 'propertyLocation');
		$this->twigVars['properties'] = EntityBase::selectDistinct($em, 'Business', 'property');
		
		$this->twig->display('admin-businesslist.twig', $this->twigVars);
	}
	
	private function outputListFile() {
		ob_start();
		
		$file = fopen("php://output", 'w');
		
		$header = array(
			'businessID', 
			'parentBusinessID', 
			'costCenter', 
			'processLevel', 
			'propertyReportsTo', 
			'propertyLocation', 
			'type', 
			'name', 
			'displayName', 
			'directorID', 
			'vpID', 
			'property', 
			'directions', 
			'intranetURL', 
			'webURL', 
			'hours', 
			'promoLine', 
			'created', 
			'lastUpdated', 
			'isBlind', 
			'isActive', 
			'isNew', 
			'publicWebsite', 
			'employeePortal', 
			'midasID', 
			'source',
			'director',
			'vp',
			'employeeCount',
			'isParent',
			'childCount');
		fputcsv($file, $header);
		
		$em = $this->getEntityManager();
		
		// Get the parents
		$sql = "SELECT b.*, 
				(SELECT CONCAT(firstName, ' ', lastName) FROM employees WHERE employeeID = b.directorID) AS director, 
				(SELECT CONCAT(firstName, ' ', lastName) FROM employees WHERE employeeID = b.vpID) AS vp, 
				(SELECT COUNT(*) FROM employees WHERE employees.costCenter = b.costCenter) AS employeeCount, 
				'1' AS isParent, 
				(SELECT COUNT(*) FROM businesses bb WHERE bb.parentBusinessID = b.businessID) AS children 
				FROM businesses AS b WHERE b.parentBusinessID = 0 ORDER BY name ASC";
		$stmt = $em->getConnection()->prepare($sql);
		$stmt->execute();
		$parents = $stmt->fetchAll();
		
		// Get the children
		foreach ($parents as &$parent) {
			fputcsv($file, $parent);
			
			$sql = "SELECT b.*, 
					(SELECT CONCAT(firstName, ' ', lastName) FROM employees WHERE employeeID = b.directorID) AS director, 
					(SELECT CONCAT(firstName, ' ', lastName) FROM employees WHERE employeeID = b.vpID) AS vp, 
					(SELECT COUNT(*) FROM employees WHERE employees.costCenter = b.costCenter) AS employeeCount, 
					'0' AS isParent 
					FROM businesses AS b WHERE b.parentBusinessID = {$parent['businessID']}";
			$stmt = $em->getConnection()->prepare($sql);
			$stmt->execute();
			$children = $stmt->fetchAll();
			
			foreach ($children as $child) {
				fputcsv($file, $child);
			}
		}
		
		fclose($file);
		
		header("Content-Type: text/csv");
		header("Content-disposition: attachment; filename=business_list.csv");
		header('Expires: 0');
		header('Cache-Control: no-cache');
		header('Content-Length: '. ob_get_length());
		
		ob_end_flush();
		exit();
	}
}