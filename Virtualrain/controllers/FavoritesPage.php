<?php

require_once( 'Page.php' );
require_once( 'VirtualRainDB.php' );

class FavoritesPage extends Page {
	public function __construct( $template ) {
		parent::__construct( $template );
		
		$this->handlePost();
		
		$db = new VirtualRainDB();
		$favorites = $db->getUserFavorites( $_SESSION[ 'user' ][ 'dist_id' ], $_SESSION['user']['id'] );
		$this->smarty->assign( 'favorites', $favorites );
	}
	
	protected function handlePost() {
		if( !empty( $_POST ) && isset( $_POST[ 'action' ]) ) {
			switch( $_POST[ 'action' ] ) {
				case 'delete_favorite': {
					$db = new VirtualRainDB();
					$db->removeUserFavorite( $_SESSION[ 'user' ][ 'id' ], $_POST[ 'sku' ], $_POST[ 'style_num' ] );
				}
			}
		}
	}
}