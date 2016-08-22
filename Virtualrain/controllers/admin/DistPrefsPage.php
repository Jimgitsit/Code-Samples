<?php

require_once ( 'AdminPage.php' );
require_once ( 'VirtualRainDB.php' );
require_once ( 'Util.php' );

class DistPrefsPage extends AdminPage {
	public function __construct( $template ) {
		parent::__construct( $template, 'Preferences' );
		
		$this->handlePost();
		
		$db = new VirtualRainDB();
		$dist = $db->getDistributer($_SESSION['distributer']['id']);
		$this->smarty->assign('dist', $dist);
		
		$distLogins = $db->getDistributerLogins($_SESSION['distributer']['id']);
		$this->smarty->assign('distLogins', $distLogins);
	}

	public function handlePost() {
		if( !empty( $_POST ) && isset( $_POST[ 'action' ] ) ) {
			$db = new VirtualRainDB();
			switch( $_POST[ 'action' ] ) {
				case 'save_prefs': 
					$dist = $db->getDistributer($_SESSION['distributer']['id']);
					
					foreach($_POST as $pref => $value) {
						if(isset($dist[$pref])) {
							$dist[$pref] = $value;
						}
					}
					
					$db->updateDistributer($dist);
					
					// Update the distributer in the session
					if(isset($_SESSION['distributer']['branch'])) {
						$branch = $_SESSION['distributer']['branch'];
					}
					$_SESSION['distributer'] = $dist;
					if($branch != null) {
						$_SESSION['distributer']['branch'] = $branch;
					}
					
					break;
				case 'ajax_save_dist_login':
					$response = array('success' => false);
					
					if ($_POST['mode'] == 'edit') {
						$response['success'] = $db->updateDistributerLogin($_POST['id'], $_POST['name'], $_POST['email'], $_POST['pw'], $_POST['emailPref']);
					}
					else if ($_POST['mode'] == 'add') {
						$response['success'] = $db->addDistributerLogin($_SESSION['distributer']['id'], $_POST['name'], $_POST['email'], $_POST['pw'], $_POST['emailPref']);
					}
					
					echo(json_encode($response));
					exit();
				case 'ajax_delete_dist_login':
					$response = array('success' => false);

					$response['success'] = $db->deleteDistributerLogin($_POST['id']);
					
					echo(json_encode($response));
					exit();
			}
		}
	}
}