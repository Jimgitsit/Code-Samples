<?php

require_once ( 'AdminPage.php' );
require_once ( 'VirtualRainDB.php' );
require_once ( 'Util.php' );

class BranchEditPage extends AdminPage {
	public function __construct( $template ) {
		parent::__construct( $template, 'Branches' );
		
		$this->handlePost();
		$this->handleGet();
		
		if(empty($_GET['id'])) {
			header('Location:branches');
			exit();
		}
		
		if($_GET['id'] != 'new') {
			$db = new VirtualRainDB();
			$branch = $db->getBranch( $_GET['id'] );
			if($branch == null) {
				header('Location:branches');
				exit();
			}
			$this->smarty->assign( 'branchEdit', $branch );
			
			$locations = $db->getPickupLocations($_SESSION['distributer']['id'], $branch['id']);
			foreach($locations as &$loc) {
				$loc['formatted'] = Util::formatLocation($loc);
			}
			$this->smarty->assign('locations', $locations);
		}
	}

	public function handleGet() {
		
	}

	public function handlePost() {
		if( !empty( $_POST ) && isset( $_POST[ 'action' ] ) ) {
			switch( $_POST[ 'action' ] ) {
				case 'save_branch': {
					$branch = $_POST;
					
					$db = new VirtualRainDB();
					
					if($_POST['mode'] == 'new') {
						unset($branch['id']);
						
						if($db->emailInUse($branch['manager_email'])) {
							$this->setAlert('Error', "The email address '{$branch['manager_email']}' is already in use.");
							break;
						}
					}
					
					$branch['dist_id'] = $_SESSION['distributer']['id'];
					
					// TODO: Hash the password
					
					
					if($db->addOrUpdateBranch($branch)) {
						header('Location:/admin/branches');
						exit();
					}
					break;
				}
			}
		}
	}
}