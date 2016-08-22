<?php

require_once( 'Page.php' );

class OrderPage extends Page {
	const ORDERS_PER_PAGE = 10;
	
	public function __construct( $template ) {
		parent::__construct( $template );
		
		$this->smarty->assign( 'ordersPerPage', self::ORDERS_PER_PAGE );
		
		$this->handlePost();
		
		if( empty( $_GET['id'] ) ){	
			$this->initOrders();
		}
		else {
			$db = new VirtualRainDB();
			$order = $db->getOrder( $_GET[ 'id' ] );
			$this->smarty->assign( 'order', $order );
		}
		
		
	}
	
	private function handlePost() {
		if( !empty( $_POST ) && isset( $_POST[ 'action' ] ) ) {
			switch( $_POST[ 'action' ] ) {
				case 'more_orders_ajax': {
					$json = $this->initOrders( true );
					echo( $json );
					exit();
				}
				case 'cancel_order_ajax':
					$response = array();

					$db = new VirtualRainDB();
					$response['success'] = $db->setOrderStatus($_POST['order_id'], 'Canceled');
					
					echo(json_encode($response));
					exit();
			}
		}
	}
	
	
	private function initOrders( $returnJSON = false ) {
		$limit = self::ORDERS_PER_PAGE;
		if( isset( $_POST[ 'offset' ] ) ) {
			$limit = $_POST[ 'offset' ] . ',' . $limit;
		}
		$db = new VirtualRainDB();
		$orders = $db->getUserOrders( $_SESSION['user']['id'], $limit );
		if( $returnJSON ) {
			$json = json_encode( $this->polishOrders( $orders ) );
			return $json;
		}
		else {
			$this->smarty->assign( 'orders', $this->polishOrders($orders) );
		}
		
	}
	
	private function polishOrders( $orders ) {
		foreach( $orders as &$order ) {
			// if($order['hidden'] != 1) {
				$order['total'] = round( $order['total'], 2 );
				$dateStamp = strtotime($order['local_order_date']);
				$order['local_order_date'] = strftime('%b %d, %Y %I:%M %p', $dateStamp);
				$order['id_padded'] = str_pad( $order['id'], 6, '0', STR_PAD_LEFT );
			//}
		}
		return $orders;
	}
}