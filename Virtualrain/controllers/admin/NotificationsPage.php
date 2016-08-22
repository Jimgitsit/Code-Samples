<?php
require_once ('AdminPage.php');
require_once ('VirtualRainDB.php');
class NotificationsPage extends AdminPage {
	
	public function __construct($template) {
		parent::__construct($template, 'Users');
		
		$this->handleImageUpload();
		
		$this->handlePost();
		
		$db = new VirtualRainDB();
		
		$branch = null;
		if(isset($_SESSION['distributer']['branch'])) {
			$branch = $_SESSION['distributer']['branch']['id'];
		}
		
		$users = $db->getActiveUsers($_SESSION['distributer']['id'], $branch);
		$this->smarty->assign('users', $users);
		
		$notifications = $db->getDistributorNotifications($_SESSION['distributer']['id'], $branch);
		foreach ( $notifications as &$notification ) {
			//$notification['message'] = strip_tags($notification['message'], "<br><br/>");
			$notification['message'] = str_replace("View Order", "", $notification['message']);
		}
		$this->smarty->assign('notifications', $notifications);
	}
	
	public function handlePost() {
		if (! empty($_POST) && isset($_POST['action'])) {
			$db = new VirtualRainDB();
			$data = $_POST;
			switch ($data['action']) {
				case "send_notification" :
					$data['dist_id'] = $_SESSION['distributer']['id'];
					if (($data['id'] = $db->saveNotification($data)) !== false) {
						$userString = "";
						$userIds = $data['users'];
						foreach ( $data['users'] as $user ) {
							$userName = $db->getUserById($user);
							$userString .= $userName['first_name'] . " " . $userName['last_name'] . ", ";
						}
						$data['users'] = $db->numUsersForNotification($data['id']);
						$data['date'] = date('M jS, Y');
						echo json_encode(array(
								"success" => true,
								"data" => $data
						));
					}
					else {
						echo json_encode(array(
								"success" => false,
								"data" => $data
						));
					}
					
					// TODO: send notification
					$action = "/notifications?id={$data['id']}";
					Util::sendNotification($userIds, $data['title'], $action);
					
					exit();
				default :
					break;
			}
		}
	}
	private function handleImageUpload() {
		if (!empty($_FILES['image_upload'])) {
			// Allowed extentions.
			$allowedExts = array("gif", "jpeg", "jpg", "png");
			
			// Get filename.
			$temp = explode(".", $_FILES["image_upload"]["name"]);
			
			// Get extension.
			$extension = end($temp);
			
			// An image check is being done in the editor but it is best to
			// check that again on the server side.
			if ((($_FILES["image_upload"]["type"] == "image/gif")
					|| ($_FILES["image_upload"]["type"] == "image/jpeg")
					|| ($_FILES["image_upload"]["type"] == "image/jpg")
					|| ($_FILES["image_upload"]["type"] == "image/pjpeg")
					|| ($_FILES["image_upload"]["type"] == "image/x-png")
					|| ($_FILES["image_upload"]["type"] == "image/png"))
					&& in_array($extension, $allowedExts)) {
				// Generate new random name.
				$name = sha1(microtime()) . "." . $extension;
				
				// Create the dist images directory if it does not already exist
				$outDir = SITE_DIR . '/dist/' . $_SESSION['distributer']['dir'] . '/img_uploads';
				if (!is_dir($outDir)) {
					mkdir($outDir);
				}
	
				// Save file in the dist images folder.
				move_uploaded_file($_FILES["image_upload"]["tmp_name"], $outDir . '/' . $name);
	
				// Generate response.
				$response = new StdClass;
				$response->link = PROTOCOL . '://' . DOMAIN . '/dist/' . $_SESSION['distributer']['dir'] . '/img_uploads/' . $name;
				echo stripslashes(json_encode($response));
				exit();
			}
		}
	}
}








