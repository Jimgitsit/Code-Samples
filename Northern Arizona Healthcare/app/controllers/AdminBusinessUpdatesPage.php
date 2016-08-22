<?php

class AdminBusinessUpdatesPage extends BasePageController {
	
	public function defaultAction() {
		
		$this->handlePost();
		
		$em = $this->getEntityManager();
		
		$newDepts = $em->getRepository('Business')->findBy(array('isNew' => true), array('created' => 'DESC'));
		$inactiveDepts = $em->getRepository('Business')->findBy(array('isActive' => false), array('lastUpdated' => 'DESC'));
		
		$this->twigVars["newDepts"] = $newDepts;
		$this->twigVars["inactiveDepts"] = $inactiveDepts;
		
		$this->twigVars['postVars'] = $this->post;
		
		$this->twigVars['businessTypes'] = EntityBase::selectDistinct($em, 'Business', 'type');
		$this->twigVars['propertyReportsTo'] = EntityBase::selectDistinct($em, 'Business', 'propertyReportsTo');
		$this->twigVars['propertyLocations'] = EntityBase::selectDistinct($em, 'Business', 'propertyLocation');
		$this->twigVars['properties'] = EntityBase::selectDistinct($em, 'Business', 'property');
		
		$this->twig->display('admin-businessupdates.twig', $this->twigVars);
	}
	
	private function handlePost() {
		if (!empty($this->post) && !empty($this->post['action'])) {
			switch ($this->post['action']) {
				case 'search-businesses':
					$phrase = trim($this->post['business_search_phrase']);
					if (!empty($phrase)) {
						$data = array(
							'search-phrase' => $phrase,
							'max-results' => 50,
							'include-inactive' => true
						);
						$ds = new NAHDirectoryService(true);
						$this->twigVars["searchResults"] = $ds->searchBusinesses($data);
					}
					else {
						header("location:/admin/businessupdates");
						exit();
					}
					break;
			}
		}
	}
}