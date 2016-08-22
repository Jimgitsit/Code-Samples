<?php

require_once( 'Page.php' );
require_once( 'classes/SessionCart.php' );

class CategoriesPage extends Page {
	public function __construct( $template ) {
		parent::__construct( $template );

		$db = new VirtualRainDB();
		
		if (!empty($_GET['gcm_reg_id'])) {
			// Save user gcm registration id (for push notifications)
			$db->setUserGCMRegId($_SESSION['user']['id'], $_GET['gcm_reg_id']);
		}
		
		if (!empty($_GET['apns_id'])) {
			// Save iOS apns_id (for push notifications)
			$db->setUserAPNSId($_SESSION['user']['id'], $_GET['apns_id']);
		}
		
		if( empty( $_GET[ 'parent_id' ] ) ) {
			$this->resetHistory();
			//$this->setHistory();
			$this->smarty->assign( 'hideBackButton', true );
		}
		
		$level = 1;
		$parentId = null;
		$this->smarty->assign( 'catName', 'CATEGORIES' );
		if( !empty( $_GET[ 'parent_id' ] ) ) {
			$parentId = $_GET[ 'parent_id' ];
			$level = null;
			
			$catName = $db->getDistCategoryName( $_SESSION[ 'dist_id' ], $parentId );
			$this->smarty->assign( 'catName', $catName );
			
			$this->smarty->assign('parentId', $parentId);
		}
		
		$categories = $db->getDistCategories( $_SESSION[ 'dist_id' ], $parentId, $level );
		if( count( $categories ) == 0 && $parentId != null ) {
			$this->removeLastHistory();
			header( "Location: list?cat_id=$parentId" );
			exit();
		}
		$this->smarty->assign( 'categories', $categories );
		
		// TODO: What is this for?
		$this->smarty->assign( 'list', 'inactive' );
		
		$this->smarty->assign('imgBaseURL', IMAGES_BASE_URL);
	}
}