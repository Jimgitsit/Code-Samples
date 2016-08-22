<?php

require_once('AdminPage.php');
require_once('VirtualRainDB.php');
require_once('Util.php');
require_once('PHPExcel.php');

class UsersPage extends AdminPage {
	private $scriptStart;
	private $scriptEnd;
	private $dbStart;
	private $dbEnd;

	public function __construct( $template ) {
		$this->scriptStart = microtime();
		parent::__construct( $template, 'Users' );
		
		$this->handlePost();
		
		$db = new VirtualRainDB();
		
		$branch = null;
		if(isset($_SESSION['distributer']['branch'])) {
			$branch = $_SESSION['distributer']['branch']['id'];
		}
		
		$sort = null;
		if(isset($_GET['sort'])) {
			$sort = explode('-', $_GET['sort']);
			$sort = array($sort[0] => $sort[1]);
		}
		
		$users = $db->getUsers( $_SESSION[ 'distributer' ][ 'id' ], $branch, false, $sort);
		$this->smarty->assign( 'users', $users );
		
		$branches = $db->getAllBranches( $_SESSION[ 'distributer' ][ 'id' ] );
		$this->smarty->assign( 'branches', $branches );
	}

	public function handlePost() {
		if( !empty( $_POST ) && isset( $_POST[ 'action' ] ) ) {
			$db = new VirtualRainDB();
			switch( $_POST[ 'action' ] ) {
				case 'save_user_ajax': {
					$user = $db->getUserById( $_POST[ 'id' ] );
					$sendActivatedEmail = false;
					if( $user[ 'status' ] == 0 && $_POST[ 'status' ] == 1 ) {
						$sendActivatedEmail = true;
					}
					
					$this->dbStart = microtime();
					unset($_POST['action']);
					if( $db->updateUser( $_POST ) ) {
						$this->dbEnd = microtime();
						if( $sendActivatedEmail ) {
							// Send the acount activated email
							$html = file_get_contents( 'templates/admin/res/user_account_activated.html' );
							$text = file_get_contents( 'templates/admin/res/user_account_activated.txt' );
							$dist = $db->getDistributer( $user[ 'dist_id' ] );
							$html = str_replace( '{dist_name}', $dist[ 'company_name' ], $html );
							$text = str_replace( '{dist_name}', $dist[ 'company_name' ], $text );
							
							Util::sendEmail( $user[ 'email' ], null, $dist[ 'company_name' ] . ' account activated', $html, $text, null, $dist[ 'company_name' ] );
						}
						$this->scriptEnd = microtime();
						$data = $_POST;
						
						// $data['price_cat'] = $db->getPriceCategory($data['price_cat_id']);
						// $data['price_cat'] = $data['price_cat']['name'];
						
						echo ( json_encode( array(
								'success' => 'true',
								"data" => $data,
								"stats" => array(
										"scriptRunTime" => ( $this->scriptEnd - $this->scriptStart ),
										"dbRunTime" => ( $this->dbEnd - $this->dbStart ) 
								) 
						) ) );
						exit();
					}
					else {
						echo( json_encode( array(
							'success' => 'false',
							'errorMsg' => $db->getLastError()
						)));
						exit();
					}
					break;
				}
				case 'remove_user_ajax': {
					if (isset($_POST['id'])) {
						$db->markDistUserRemoved(true, $_POST['id']);
					}
					exit();
				}
				case 'restore_user_ajax': {
					if (isset($_POST['id'])) {
						$db->markDistUserRemoved(false, $_POST['id']);
					}
					exit();
				}
				case 'save_all_users': {
					break;
				}
			}
		}
	}

}