<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['mongo_server'] = 'mongodb://localhost:27017';
// $config['mongo_dbname'] = 'if-import';
if (ENVIRONMENT == 'development') {
	$config['mongo_dbname'] = 'instafluence-dev';
}
else {
	$config['mongo_dbname'] = 'instafluence';
}
