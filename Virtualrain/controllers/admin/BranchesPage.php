<?php

require_once ( 'AdminPage.php' );
require_once ( 'VirtualRainDB.php' );

class BranchesPage extends AdminPage {
	public function __construct( $template ) {
		parent::__construct( $template, 'Branches' );
		
		$this->handlePost();
		
		$db = new VirtualRainDB();
		$branches = $db->getAllBranches( $_SESSION[ 'distributer' ][ 'id' ] );
		$this->smarty->assign( 'branches', $branches );
	}

	public function handlePost() {
		if( !empty( $_POST ) && isset( $_POST[ 'action' ] ) ) {
			switch( $_POST[ 'action' ] ) {
				case 'delete_branch_ajax': {
					$response = array('success' => true);
					
					$db = new VirtualRainDB();
					$db->deleteBranch($_POST['id']);
					
					echo(json_encode($response));
					exit();
				}
			}
		}
	}
}