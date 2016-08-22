<?php 

require_once( 'AdminPage.php' );
require_once( 'VirtualRainDB.php' );
require_once( 'PHPExcel.php' );

class OrderExportPage extends AdminPage {
	public function __construct() {
		parent::__construct( null, 'Order Details' );
		
		if(!isset($_GET['id'])) {
			echo('Missing order ID');
			exit();
		}

		$branchId = null;
		if(isset($_SESSION['distributer']['branch'])) {
			$branchId = $_SESSION['distributer']['branch']['id'];
		}
		
		$padId = str_pad($_GET['id'], 6, '0', STR_PAD_LEFT);
		
		$excel = new PHPExcel();
		$excel->getProperties()->setTitle("Order " . $padId);
		$excel->setActiveSheetIndex(0);
		$sheet = $excel->getActiveSheet();
		$sheet->setTitle("Order " . $padId);

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

		$db = new VirtualRainDB();
		$order = $db->getOrder($_GET['id']);

		$user = $db->getUserById($order['user_id']);

		$row = 2;
		foreach($order['cart'] as $item) {
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
		
		/* Old style
		$headerRow = array(
			'A1' => '#',
			'B1' => 'Item',
			// TODO: This depends on dist prefs
			'C1' => 'SKU',
			'D1' => 'Qty'
		);
		
		foreach($headerRow as $pos => $hr) {
			$sheet->setCellValue($pos, $hr);
		}
		
		$db = new VirtualRainDB();
		$order = $db->getOrder($_GET['id']);
		
		$row = 2;
		foreach($order['cart'] as $item) {
			$cols = array(
				"A$row" => $row - 1,
				"B$row" => $item['product']['title'],
				"C$row" => $item['product']['sku'],
				"D$row" => $item['quantity'],
			);
			
			
			foreach($cols as $pos => $val) {
				$sheet->setCellValue($pos, $val);
			}
			
			$row++;
		}
		*/
		
		$filename = "Order_" . $padId . '.xlsx';
		
		header('Content-type: application/vnd.ms-excel');
		header('Content-Disposition: attachment; filename="Order_' . $padId . '.xlsx"');
		
		$writer = new PHPExcel_Writer_Excel2007($excel);
		$writer->save('php://output');
	}
}