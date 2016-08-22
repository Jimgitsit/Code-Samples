<?php

require_once( 'AdminPage.php' );
require_once( 'VirtualRainDB.php' );
require_once( 'Util.php' );

class OrdersPage extends AdminPage {
	const MAX_ORDERS_PER_PAGE = 80;

	private $orderStatuses = array(
		'Placed',
		'Received',
		'Processed',
		'Shipped',
		'Ready for pickup',
		'Back Ordered',
		'Canceled'
	);

	public function __construct( $template ) {
		parent::__construct( $template, 'Orders' );

		$this->handlePost();
		
		$db = new VirtualRainDB();
		
		// Get the branch id
		$branchId = null;
		if(isset($_SESSION['distributer']['branch'])) {
			$branchId = $_SESSION['distributer']['branch']['id'];
		}
		
		// Get the filter params
		$filterQuery = '';
		$filters = null;
		if(isset($_GET['filters'])) {
			$temp = $_GET['filters'];
			foreach($temp as $name => $value) {
				if($value != '') {
					$filters[$name] = $value;
					
					// TODO: Probably want to validate the start and end dates
				}
			}
			
			$filterQuery = http_build_query(array('filters' => $_GET['filters']));
		}
		
		$this->smarty->assign('showRemoved', '');
		if(isset($_GET['removed'])) {
			$filters['hidden'] = true;
			$this->smarty->assign('showRemoved', '&removed=1');
		}

		$this->smarty->assign('filterQuery', $filterQuery);
		
		// Get the total order count
		$total = $db->getDistributerOrders( $_SESSION[ 'distributer' ][ 'id' ], $branchId, $filters, null, true );
		$this->smarty->assign( 'totalOrders', $total );
		
		// *** paging ***
		$count = self::MAX_ORDERS_PER_PAGE;
		$reqPage = 1;
		if( isset( $_GET[ 'p' ] ) ) {
			$reqPage = $_GET[ 'p' ];
		}
		$offset = $count * ( $reqPage - 1 );
		
		$prevPage = $reqPage - 1;
		$nextPage = $reqPage + 1;
		
		$pageEnd = $offset + $count;
		if( $pageEnd > $total ) {
			$pageEnd = $total;
			$nextPage = 0;
		}
		
		$this->smarty->assign( 'pageStart', $offset + 1 );
		$this->smarty->assign( 'pageEnd', $pageEnd );
		$this->smarty->assign( 'prevPage', $prevPage );
		$this->smarty->assign( 'nextPage', $nextPage );
		// *** end paging ***
		
		// Get all users for this distributer and branch
		//$users = $db->getUsers($_SESSION[ 'distributer' ][ 'id' ], $branchId, true);
		$users = $db->getUsers($_SESSION[ 'distributer' ][ 'id' ], null, true);
		$this->smarty->assign('users', $users);
		
		$sort = null;
		if(isset($_GET['sort'])) {
			$sort = explode('-', $_GET['sort']);
			$sort = array($sort[0] => $sort[1]);
		}
		
		// Get the orders
		$orders = $db->getDistributerOrders( $_SESSION[ 'distributer' ][ 'id' ], $branchId, $filters, $sort, false, $offset, $count );

		foreach( $orders as &$order ) {
			//$order[ 'user' ] = $db->getUserById( $order[ 'user_id' ] );
			$order['user'] = $users[$order['user_id']];
			$order[ 'branch' ] = null;
			if($order['branch_id'] !== null && $order['branch_id'] != 0) {
				$order[ 'branch' ] = $db->getBranch($order['branch_id']);
			}
			/*
			if( $order['pickup_location'] != null && $order['pickup_location'] != 0 ){
				$order['address'] = $db->getPickupLocationById( $order['pickup_location'] );
			}
			else if( $order['shipping_location'] != null && $order['shipping_location'] != 0 ){
				$order['address'] = $db->getShippingLocationById( $order['shipping_location'] );
			}
			*/
		}
		
		$this->smarty->assign( 'orders', $orders );
		
		// Get all order statuses
		$orderStatuses = $db->getOrderStatuses( $_SESSION[ 'distributer' ][ 'id' ] );
		$this->smarty->assign( 'orderStatuses', $orderStatuses );
		
		// Get all company names for this distributer and branch
		$companyNames = $db->getCompanyNames($_SESSION['distributer']['id'], $branchId);
		$this->smarty->assign('companyNames', $companyNames);
		
		// Get dist prefs
		$this->smarty->assign('showStyle', $_SESSION[ 'distributer' ]['show_style_on_orders']);
		$this->smarty->assign('showSKU', $_SESSION[ 'distributer' ]['show_sku_on_orders']);
		$this->smarty->assign('showPN', $_SESSION[ 'distributer' ]['show_product_number_on_orders']);
		
	}

	private function handlePost() {
		if( !empty( $_POST ) && isset( $_POST[ 'action' ] ) ) {
			switch( $_POST[ 'action' ] ) {
				case 'change_order_status_ajax': {
					if( isset( $_POST[ 'order_id' ] ) && isset( $_POST[ 'new_status' ] ) ) {
						$db = new VirtualRainDB();
						if( $db->setOrderStatus( $_POST[ 'order_id' ], $_POST[ 'new_status' ] ) ) {
							$pickupMsg = '';
							if( $_POST[ 'new_status' ] == 'Waiting for pickup' ) {
								$order = $db->getOrder( $_POST[ 'order_id' ] );
								$pickupMsg = '<br><br>Pickup location:<br><br>' . Util::formatLocation( $db->getPickupLocationbyId( $order[ 'pickup_location' ] ) );
							}

							// Send a message to the user
							$data = array(
								'title' 	=> 'Order ' . str_pad( $_POST[ 'order_id' ], 6, '0', STR_PAD_LEFT ) . ' - ' . $_POST[ 'new_status' ],
								'message' 	=> 'The status for order ' . str_pad( $_POST[ 'order_id' ], 6, '0', STR_PAD_LEFT ) . ' has been changed to ' . $_POST[ 'new_status' ] . $pickupMsg . '.<br><br><a href="order?id=' . $_POST[ 'order_id' ] . '" class="message-link">View Order</a>',
								'dist_id' 	=> $_SESSION[ 'distributer' ][ 'id' ],
								'users'		=> array( $_POST[ 'user_id' ] )
							);
							$db->saveNotification( $data );

							$msg = 'Order #' . str_pad( $_POST[ 'order_id' ], 6, '0', STR_PAD_LEFT ) . ' status: ' . $_POST[ 'new_status' ];
							
							// Send an email to the user
							$user = $db->getUserById($_POST['user_id']);
							
							if($user['status_emails']) {
								Util::sendEmail($user['email'], null, $data['title'], $msg, $msg, null, $_SESSION['distributer']['company_name']);
							}
							
							// TODO: Send notification
							Util::sendNotification($user['id'], $msg, "/order?id={$_POST[ 'order_id' ]}");
							
							echo( 'success' );
						}
						else {
							echo( 'failed' );
						}
					}

					exit();
				}
				case 'add_statuses_ajax': {
					$response = array( "success" => true );
					if( !empty( $_POST[ 'statuses' ] ) && is_array( $_POST[ 'statuses' ] ) ) {
						$db = new VirtualRainDB();
						$db->deleteAllOrderStatuses( $_SESSION[ 'distributer' ][ 'id' ] );
						foreach( $_POST[ 'statuses' ] as $status ) {
							$db->addOrderStatus( $_SESSION[ 'distributer' ][ 'id' ], $status );
						}
					}
					else {
						$response[ "success" ] = false;
					}
					
					echo( json_encode( $response ) );
					exit();
				}
				case 'remove_order_ajax': {
					$db = new VirtualRainDB();
					$db->setOrderHidden($_POST['id'], 1);
					exit();
				}
				case 'restore_order_ajax': {
					$db = new VirtualRainDB();
					$db->setOrderHidden($_POST['id'], 0);
					exit();
				}
			}
		}
	}
}