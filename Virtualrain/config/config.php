<?php

//define('GOOGLE_PUBLIC_API_KEY', 'AIzaSyDPbJxRqm-hSjPw9yBguiqWj_vk2rMgtUI');
define('GOOGLE_PUBLIC_API_KEY', 'AIzaSyAw1SpizEFMqDzOsAxJkRFVyUBoCZr2tz8');

// *** Set up include paths ***
$paths = array( 
		'config',
		'classes',
		'templates',
		'controllers',
		'libs',
		'libs/smarty/libs',
		'libs/phpmailer',
		'libs/phpexcel',
		'services',
		'libs/ApnsPHP'
);

$addPath = '';
foreach( $paths as $path ) {
	$addPath .= $path . PATH_SEPARATOR;
}
set_include_path( get_include_path() . PATH_SEPARATOR . $addPath );

define('IMAGES_BASE_URL', "http://images.vrmobileapp.com/");

if (strstr($_SERVER['HTTP_HOST'], 'vrmobileapp') !== false) {
	define('LEGACY_HOST_NAME', true);
}
else {
	define('LEGACY_HOST_NAME', false);
}

require_once( 'sendmail_config.php' );
require_once( 'config_site.php' );

// SITE_DIR must be defined in config_site.php and it is the full path on the
// current machine where the root site dir is located.
//echo(SITE_DIR);
chdir( SITE_DIR );

// Set session timeout to 30 days
ini_set('session.gc_maxlifetime', 2678400);