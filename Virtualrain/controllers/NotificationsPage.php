<?php

require_once( 'Page.php' );

class NotificationsPage extends Page {
	const NOTIFICATIONS_PER_PAGE = 10;
	
	public function __construct( $template ) {
		parent::__construct( $template );
		
		$this->smarty->assign( 'notificationsPerPage', self::NOTIFICATIONS_PER_PAGE );
		
		$this->handlePost();
		
		if( empty( $_GET['id'] ) ){	
			$this->initNotifications();
			
		}
		else if ( isset( $_GET['id'] ) ){
			$db = new VirtualRainDB();
			$message = $db->getUserNotification( $_GET[ 'id' ] );
			$message['message'] = str_replace('<a', '<a target="_parent"', $message['message']);
			$this->smarty->assign( 'message', $message );
		}
	}
	
	private function handlePost() {
		if( !empty( $_POST ) && isset( $_POST[ 'action' ] ) ) {
			switch( $_POST[ 'action' ] ) {
				case 'more_notifications_ajax': {
					$json = $this->initNotifications( true );
					echo( $json );
					exit();
				}
				/*
				case 'delete_message': {
					$db = new VirtualRainDB();
					$db->deleteNotification( $_POST['id'] );
					header('Location:/notifications');
				}
				*/
			}
		}
	}
	
	private function initNotifications( $returnJSON = false ) {
		$limit = self::NOTIFICATIONS_PER_PAGE;
		if( isset( $_POST[ 'offset' ] ) ) {
			$limit = $_POST[ 'offset' ] . ',' . $limit;
		}
		$db = new VirtualRainDB();
		$notifications = $db->getUserNotifications( $_SESSION['user']['id'], $limit );
		if( $returnJSON ) {
			$json = json_encode( $this->generateListItems( $notifications ) );
			return $json;
		}
		else {
			$this->smarty->assign( 'messages', $this->generateListItems( $notifications ) );
		}
		
	}
	
	private function generateListItems( $notifications ) {
		$messages = array();
		foreach( $notifications as $message ) {
			
			$dateStamp = strtotime($message['local_date']);
			$date = date( 'F dS, Y h:i A',$dateStamp );
			if( $message['active'] == '1' ){	
				$html = "<a href=\"/notifications?id={$message['message_id']}\"><li><p>{$message['title']}</p><span class=\"msg-date\">$date</span><br class=\"clear\"/></li></a>";
			}
			else if( $message['active'] == '0' ){
				$html = "<a href=\"/notifications?id={$message['message_id']}\"><li><p class=\"read\">{$message['title']}</p><span class=\"msg-date\">$date</span><span class=\"read-message\">&#x2713;</span><br class=\"clear\"/></li></a>";
			}
			if( $html != '' ) {
				$messages[] = $html;
			}
		}
		return $messages;
	}
	
	
	
	
}