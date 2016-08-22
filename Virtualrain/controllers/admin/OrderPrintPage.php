<?php

require_once( 'AdminPage.php' );
require_once( 'VirtualRainDB.php' );

class OrderPrintPage extends AdminPage {

	public function __construct( $template ) {
		parent::__construct( $template, 'Orders' );
		
		if( !isset( $_GET['id'] ) ) {
			header( "Location: /admin/orders" );
		}

		$db = new VirtualRainDB();
		
		$order = $db->getOrder( $_GET['id'] );
		
		$order[ 'user' ] = $db->getUserById( $order[ 'user_id' ] );
		
		if( $order['pickup_location'] != null && $order['pickup_location'] != 0 ){
			$order['address'] = $db->getPickupLocationById( $order['pickup_location'] );
		}
		else if( $order['shipping_location'] != null && $order['shipping_location'] != 0 ){
			$order['address'] = $db->getShippingLocationById( $order['shipping_location'] );
		}
		
		$this->smarty->assign( 'order', $order );
		$this->smarty->assign( 'distributor', $_SESSION['distributer'] );
		
		$this->smarty->assign('showStyle', $_SESSION[ 'distributer' ]['show_style_on_orders']);
		$this->smarty->assign('showSKU', $_SESSION[ 'distributer' ]['show_sku_on_orders']);
		$this->smarty->assign('showPN', $_SESSION[ 'distributer' ]['show_product_number_on_orders']);
		$this->smarty->assign('showUnits', $_SESSION[ 'distributer' ]['show_units']);
	}
}