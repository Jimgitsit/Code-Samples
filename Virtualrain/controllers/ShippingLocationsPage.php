<?php

require_once( 'Page.php' );

class ShippingLocationsPage extends Page {
	public function __construct( $template ) {
		parent::__construct( $template );
		$this->handlePost();
		
		//Load Locations
		$db = new VirtualRainDB();
		$locations = $db->getShippingLocations( $_SESSION['user']['id'] );
		
		$this->smarty->assign( 'locations', $locations );
		//Load Preferred Locations
		$preferred = $db->getPreferredShippingLocation( $_SESSION['user']['id'] );
		$this->smarty->assign( 'preferred', $preferred['preferred_shipping_location'] );
	}
	
	protected function handlePost() {
		
		if( !empty( $_POST ) && isset( $_POST[ 'action' ] ) ) {
			$response = array('success' => true, 'msg' => '');
			switch( $_POST[ 'action' ] ) {
				case 'save_preferred_ajax': {
					$db = new VirtualRainDB();
					$db->savePreferredShipping( $_POST[ 'preferred' ], $_SESSION['user']['id'] );
					
					exit();
				}
				case 'save_new_location_ajax': {
					// Validate
					if($this->validateLocation($_POST)) {
						$db = new VirtualRainDB();
						$db->saveNewLocation( $_POST['name'], $_POST['address1'], $_POST['address2'], $_POST['city'], 
											  $_POST['state'], $_POST['zip'], $_POST['phone'], $_SESSION['user']['id'] );
					}
					else {
						$response['success'] = false;
						$response['msg'] = 'Missing required information';
					}
					
					echo(json_encode($response));
					exit();
				}
				case 'delete_shipping_location_ajax': {
					$db = new VirtualRainDB();
					$db->inactivateShippingLocation( $_POST['id'] );
					
					exit();
				}
				case 'update_location_ajax': {
					// Validate
					if($this->validateLocation($_POST)) {
						$db = new VirtualRainDB();
						$db->updateLocation( $_POST['name'], $_POST['address1'], $_POST['address2'], $_POST['city'], 
											 $_POST['state'], $_POST['zip'], $_POST['phone'], $_POST['id'] );
					}
					else {
						$response['success'] = false;
						$response['msg'] = 'Missing required information';
					}
					
					echo(json_encode($response));
					exit();
				}
			}
		}
	}

	private function validateLocation( $location ) {
		if( empty( $location[ 'name' ] ) || empty( $location[ 'address1' ] ) || empty( $location[ 'city' ] ) || empty( $location[ 'state' ] ) || empty( $location[ 'zip' ] ) ) {
			return false;
		}
		
		return true;
	}
}