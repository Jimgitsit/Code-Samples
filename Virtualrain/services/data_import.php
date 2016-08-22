<?php

chdir( dirname( __FILE__ ) );

require_once( 'DataImportService.php' );

echo( 'Current working directory: ' . getcwd() . "\n" );

$date = date( 'm-d-Y' );
if( !empty( $argv[ 1 ] ) ) {
	$date = $argv[ 1 ];
}
//$date = '11-05-2013';
echo( "Looking for products data file for the date $date\n" );

$service = new DataImportService();
if( $service->checkForNewData( $date ) ) {
	$service->importData( $date, REMOVE_MISSING_DATA );
}