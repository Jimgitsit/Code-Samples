<?php

require_once( 'Page.php' );

class LocationsPage extends Page {
	public function __construct( $template ) {
		parent::__construct( $template );
		$this->handlePost();
		
		//Load Locations
		$db = new VirtualRainDB();
		$locations = $db->getLocations( $_SESSION['dist_id'] );
		$this->smarty->assign( 'locations', $locations );
		
		//Load Preferred Locations
		$preferred = $db->getPreferredLocation( $_SESSION['user']['id'] );
		$this->smarty->assign( 'preferred', $preferred['preferred_location'] );
	}
	
	protected function handlePost() {
		
		if( !empty( $_POST ) && isset( $_POST[ 'action' ] ) ) {
			switch( $_POST[ 'action' ] ) {
				case 'save_preferred_ajax': {
					$db = new VirtualRainDB();
					$db->savePreferred( $_POST[ 'preferred' ], $_SESSION['user']['id'] );
						
					exit();
				}
			}
		}
	}
}