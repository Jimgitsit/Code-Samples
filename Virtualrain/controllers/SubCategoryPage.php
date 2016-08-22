<?php

require_once( 'Page.php' );
require_once( 'classes/SessionCart.php' );

class SubCategoryPage extends Page {
	public function __construct( $template ) {
		parent::__construct( $template );
		
		if( empty( $_GET ) || !isset( $_GET[ 'id' ] ) ) {
			header( "Location: categories" );
			exit();
		}
		
		$distId = $_SESSION[ 'dist_id' ];
		
		$db = new VirtualRainDB();
		$subCategories = $db->getDistSubCategories( $distId, $_GET[ 'id' ], false, true );
		$categoryTitle = $db->getDistCategoryName( $distId, $_GET[ 'id' ] );
		
		$this->smarty->assign( 'subCategories', $subCategories );
		$this->smarty->assign( 'catTitle', $categoryTitle );
	}
}