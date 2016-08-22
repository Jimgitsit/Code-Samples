<?php

require_once( 'AdminPage.php' );
require_once( 'VirtualRainDB.php' );

class AdminUsersPage extends AdminPage {
	public function __construct( $template ) {
		parent::__construct( $template, 'Admin Users' );

		$this->handlePost();

		$db = new VirtualRainDB();

		$admins = $db->getAdminUsers();

		$this->smarty->assign('admins',$admins);
	}


	private function handlePost(){
		if(!empty($_POST) && isset($_POST['action'])){
			$db = new VirtualRainDB();
			switch ($_POST['action']){
				case "create_new":
					$id = $db->createSuperUser($_POST['user']);
					if($id === false){
						echo json_encode(array("success"=>false));
					}else{
						echo json_encode(array("success"=>true,"id"=>$id));
					}
					exit();
					break;
				case "delete_user":
					$result = false;
					if($db->deleteSuperUser($_POST['id'])){
						$result = true;
					}
					echo json_encode(array("success"=>$result));
					exit();
					break;
				case "save_user":
					$result = false;
					$user = $_POST['user'];
					if($user['password'] == ""){
						$user['password'] = null;
					}
					if($db->saveSuperAdmin($user)){
						$result = true;
					}
					echo json_encode(array("success"=>$result));
					exit();
					break;
			}
		}
	}
}