<?php

require_once( 'AdminPage.php' );
require_once( 'VirtualRainDB.php' );
require_once('Util.php');
require_once( 'PHPExcel.php' );

class ReportsPage extends AdminPage {
	
	public function __construct( $template ) {
		parent::__construct( $template, 'Reports' );
		$this->handlePost();
	}
	
	private function handlePost() {
		if( !empty( $_POST ) && isset( $_POST[ 'action' ] ) ) {
			$db = new VirtualRainDB();
			switch( $_POST[ 'action' ] ) {
				case 'export_orders': {
					if(!isset($_POST['start_date']) || !isset($_POST['end_date'])) {
						throw new Exception("Missing start or end date.");
					}

					$branchId = null;
					if(isset($_SESSION['distributer']['branch'])) {
						$branchId = $_SESSION['distributer']['branch']['id'];
					}
					
					$excel = new PHPExcel();
					$excel->getProperties()->setTitle("Exported Orders");
					$excel->setActiveSheetIndex(0);
					$sheet = $excel->getActiveSheet();
					$sheet->setTitle("Exported Orders");
					
					$headerRow = array(
						'A' => 'Date',
						'B' => 'Order#',
						'C' => 'Status',
						'D' => 'Customer',
						'E' => 'Company',
						'F' => 'Product#',
						'G' => 'SKU',
						'H' => 'Quantity',
						'I' => 'Price',
						'J' => 'Unit'
					);
					
					if ($branchId === null) {
						$headerRow['K'] = 'Branch';
					}
					
					foreach($headerRow as $col => $hr) {
						$sheet->setCellValue($col . '1', $hr);
					}

					$orders = $db->getDistributerOrders($_SESSION['distributer']['id'], $branchId, array('start_date' => $_POST['start_date'], 'end_date' => $_POST['end_date']));
					$row = 2;
					foreach($orders as $order) {
						$user = $db->getUserById($order['user_id']);
						$cartData = json_decode($order['cart_data'], true);
						foreach($cartData as $item) {
							$sheet->setCellValue("A$row", $order['local_order_date']);
							$sheet->setCellValueExplicit("B$row", str_pad($order['id'], 6, '0', STR_PAD_LEFT), PHPExcel_Cell_DataType::TYPE_STRING);
							$sheet->setCellValue("C$row", $order['status']);
							$sheet->setCellValue("D$row", $user['first_name'] . ' ' . $user['last_name']);
							$sheet->setCellValue("E$row", $user['company_name']);
							if($_SESSION[ 'distributer' ]['show_sku_on_orders']) {
								$sheet->setCellValue("F$row", $item['product']['part_num']);
							}
							if($_SESSION[ 'distributer' ]['show_product_number_on_orders']) {
								$sheet->setCellValue("G$row", $item['product']['sku']);
							}
							
							$sheet->setCellValue("H$row", $item['quantity']);
							$sheet->setCellValue("I$row", round($item['price'], 2));
							if (isset($item['style']['unit'])) {
								$sheet->setCellValue("J$row", $item['style']['unit']);
							}
							else {
								$sheet->setCellValue("J$row", '');
							}
							
							if ($branchId === null) {
								$sheet->setCellValue("K$row", $order['branch_name'], 2);
							}
							
							$row++;
						}
					}
					
					$filename = "Orders_" . str_replace('/', '-', $_POST['start_date']) . '_' . str_replace('/', '-', $_POST['end_date']) . '.xlsx';
					
					header('Content-type: application/vnd.ms-excel');
					header('Content-Disposition: attachment; filename="' . $filename . '"');
					
					$writer = new PHPExcel_Writer_Excel2007($excel);
					$writer->save('php://output');
					
					exit();
				}
				case 'export_users':
					$excel = new PHPExcel();
					$excel->getProperties()->setTitle("Exported Users");
					$excel->setActiveSheetIndex(0);
					$sheet = $excel->getActiveSheet();
					$sheet->setTitle("Exported Users");

					$headerRow = array(
						'A' => 'Name',
						'B' => 'Email',
						'C' => 'Company',
						'D' => 'Phone',
						'E' => 'Account',
						'F' => 'Branch',
						'G' => 'Pricing',
						'H' => 'Status',
						'I' => 'Last Login'
					);

					foreach($headerRow as $col => $hr) {
						$sheet->setCellValue($col . '1', $hr);
					}

					$branch = null;
					if(isset($_SESSION['distributer']['branch'])) {
						$branch = $_SESSION['distributer']['branch']['id'];
					}

					$users = $db->getUsers( $_SESSION[ 'distributer' ][ 'id' ], $branch );
					$row = 2;
					foreach ($users as $user) {
						$sheet->setCellValue("A$row", $user['first_name'] . ' ' . $user['last_name']);
						$sheet->setCellValue("B$row", $user['email']);
						$sheet->setCellValue("C$row", $user['company_name']);
						$sheet->setCellValue("D$row", $user['cell_phone']);
						$sheet->setCellValue("E$row", $user['account_num']);

						if ($user['branch_id'] !== null) {
							$branch = $db->getBranch($user['branch_id']);
						}

						$sheet->setCellValue("F$row", $branch['name']);
						if ($user['show_pricing']) {
							$sheet->setCellValue("G$row", 'yes');
						}
						else {
							$sheet->setCellValue("G$row", 'no');
						}

						if ($user['status'] == 1) {
							$sheet->setCellValue("H$row", 'Active');
						}
						else if ($user['status'] == 3) {
							$sheet->setCellValue("H$row", 'New');
						}
						else {
							$sheet->setCellValue("H$row", 'Disabled');
						}

						$sheet->setCellValue("I$row", $user['last_login']);

						$row++;
					}

					$filename = "Users.xlsx";

					header('Content-type: application/vnd.ms-excel');
					header('Content-Disposition: attachment; filename="' . $filename . '"');

					$writer = new PHPExcel_Writer_Excel2007($excel);
					$writer->save('php://output');

					exit();
			}
		}
	}
}