<?php

require_once( 'AdminPage.php' );

class DistributersPage extends AdminPage {
	private $scriptStart;
	private $scriptEnd;
	private $dbStart;
	private $dbEnd;

	public function __construct( $template ) {
		$this->scriptStart = microtime(true);
		parent::__construct( $template, 'Distributers' );
		if(!isset($_SESSION['admin_user'])){
			session_start();
			session_destroy();
			$_SESSION = array();
			header("Location: /admin/login");
			die();
		}

		$this->handlePost();

		$db = new VirtualRainDB();
		$distributers = $db->getAllDistributers();
		$this->smarty->assign( 'distributers', $distributers );
		$this->smarty->assign( 'admin', $_SESSION[ 'admin_user' ] );
	}

	public function handlePost() {
		if( isset( $_POST[ 'action' ] ) ) {
			switch( $_POST[ 'action' ] ) {
				/*
				case 'delete_distributer':
					if( !empty( $_POST[ 'id' ] ) ) {
						$db = new VirtualRainDB();
						$success = $db->deleteDistributer( $_POST[ 'id' ] );
						echo( "{\"success\":\"$success\"}" );
						exit();
					}
					break;
				*/
				case 'login_as': {
					if(!empty($_POST['id'])){
						$db = new VirtualRainDB();
						$this->dbStart = microtime(true);
						$dist = $db->getDistributer($_POST['id']);
						$this->dbEnd = microtime(true);
						if($dist !== null){
							$_SESSION['distributer'] = $dist;
							unset($_SESSION['admin_user']);
							$this->scriptEnd = microtime(true);
							echo json_encode(array("success"=>true,"scriptRunTime"=>($this->scriptEnd-$this->scriptStart),"dbRunTime"=>($this->dbEnd-$this->dbStart)));
						}else{
							echo json_encode(array("success"=>false));
						}
					}else{
						echo json_encode(array("success"=>false));
					}
					exit();
				}
			}
		}
	}
}