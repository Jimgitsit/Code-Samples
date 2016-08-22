<?php

require_once( 'AdminPage.php' );
require_once( 'VirtualRainDB.php' );

class LocationsPage extends AdminPage {
	public function __construct( $template ) {
		parent::__construct( $template, 'Users' );

		$this->handlePost();

		$db = new VirtualRainDB();

		$locations = $db->getAllLocations($_SESSION['distributer']['id']);
		foreach($locations as &$location) {
			if($location['branch_id'] != null && $location['branch_id'] != 0) {
				$branch = $db->getBranch($location['branch_id']);
				$location['branch_name'] = $branch['name'];
			}
			else {
				$location['branch_name'] = '';
			}
		}
		$this->smarty->assign('locations', $locations);
		
		$branches = $db->getAllBranches( $_SESSION[ 'distributer' ][ 'id' ] );
		$this->smarty->assign( 'branches', $branches );
	}

	public function handlePost(){
		if( !empty( $_POST ) && isset( $_POST[ 'action' ] ) ) {
			$data = $_POST;
			switch($data['action']){
				case "create_location":
					echo json_encode($this->createLocation($data['location']));
					exit();
				case "save_location":
					echo json_encode($this->saveLocation($data['location']));
					exit();
				case "remove_location":
					echo json_encode($this->deleteLocation($data['location']));
					exit();
				default:
					break;
			}
		}
	}

	private function saveLocation($location){
		$db = new VirtualRainDB();
		return $db->saveLocation($location);
	}

	private function deleteLocation($location){
		$db = new VirtualRainDB();
		return $db->deleteLocation($location);
	}

	private function createLocation($location){
		$db = new VirtualRainDB();
		$location['dist_id'] = $_SESSION['distributer']['id'];
		return $db->createLocation($location);
	}
}