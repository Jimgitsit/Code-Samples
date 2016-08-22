<?php

require_once( 'AdminPage.php' );
require_once( 'DataImportService.php' );
require_once( 'VirtualRainDB.php' );
require_once( 'PHPExcel.php' );

class AdminDataPage extends AdminPage {
	public function __construct( $template ) {
		parent::__construct( $template, 'Admin Users' );

		$this->handlePost();
	}

	private function handlePost() {
		if( !empty( $_POST ) && isset( $_POST[ 'action' ] ) ) {
			switch( $_POST[ 'action' ] ) {
				case 'export_master_product_data': {
					// Remove any previous files
					$dataDir = SITE_DIR . "/" . DATA_DEST_DIR;
					$files = scandir( $dataDir );
					if( $files == false ) {
						throw new Exception( "Error reading data directory. " . $dataDir );
					}
					foreach( $files as $file ) {
						if( strstr( $file, 'Master-Products_' ) ) {
							unlink( $dataDir . "/" . $file );
						}
					}
					
					$fileName = 'Master-Products_' . date( 'Ymd_His' ) . '.xlsx';
					$response = array( 'success' => true, 'msg' => '', 'file_name' => $fileName, 'warnings' => array() );
					
					try {
						$excel = new PHPExcel();
						
						$prodSheet = $excel->getSheet();
						$prodSheet->setTitle( "Products" );
						$col = 0;
						foreach( DataImportService::$masterExportHeaders as $header ) {
							$prodSheet->setCellValueByColumnAndRow( $col, 1, $header );
							$col++;
						}
						
						$db = new VirtualRainDB();
						$products = $db->getAllProducts();
						$styles = $db->getProductStyles( $products, null, false );
						
						$currRow = 2;
						foreach( $products as $product ) {
							if( isset( $styles[ $product[ 'id' ] ] ) ) {
								$prodStyles = $styles[ $product[ 'id' ] ];
								foreach( $prodStyles as $style ) {
									$prodSheet->setCellValueByColumnAndRow( 0, $currRow, $product[ 'title' ] );
									$prodSheet->setCellValueByColumnAndRow( 1, $currRow, $product[ 'manufacturer' ] );
									$prodSheet->setCellValueByColumnAndRow( 2, $currRow, $product[ 'manufacturer_description' ] );
									$prodSheet->setCellValueByColumnAndRow( 3, $currRow, $style[ 'style_description' ] );
									$prodSheet->setCellValueByColumnAndRow( 4, $currRow, $style[ 'price' ] );
									if( count( $prodStyles ) == 1 || $style[ 'default_style' ] == 1 ) {
										$prodSheet->setCellValueByColumnAndRow( 5, $currRow, 'yes' );
									}
									else {
										$prodSheet->setCellValueByColumnAndRow( 5, $currRow, 'no' );
									}
									$prodSheet->setCellValueByColumnAndRow( 6, $currRow, $product[ 'part_num' ] );
									$prodSheet->setCellValueByColumnAndRow( 7, $currRow, $style[ 'style_num' ] );
									$prodSheet->setCellValueByColumnAndRow( 8, $currRow, $style[ 'full_sku' ] );
									$prodSheet->setCellValueByColumnAndRow( 9, $currRow, $style[ 'upc' ] );
									$currRow++;
								}
							}
							else {
								$response[ 'warnings' ][] = "Found product with no styles: ({$product[ 'id' ]}) \"{$product[ 'title' ]}\"";
							}
						}
						
						$write = PHPExcel_IOFactory::createWriter( $excel, "Excel2007" );
						$write->save( SITE_DIR . "/" . DATA_DEST_DIR . "/" . $fileName );
						
						// Cleanup
						$excel->disconnectWorksheets();
						unset( $excel );
					}
					catch( Exception $e ) {
						$response[ 'success' ] = false;
						$response[ 'msg' ] = $e->getMessage();
					}
					
					echo( json_encode( $response ) );
					exit();
				}
			}
		}
	}
}