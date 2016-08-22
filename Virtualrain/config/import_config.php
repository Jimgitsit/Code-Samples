<?php

// This is needed for the DB_* defines
require_once( 'config_site.php' );

// Log file
define( 'LOG_FILE', SITE_DIR . '/logs/vrain_data_import.log' );
		
// This is either 'local' or 'ftp'
define( 'DATA_LOCATION', 'local' );

// FTP info if DATA_LOCATION == 'ftp'
define( 'FTP_USER', '' );
define( 'FTP_PASS', '' );
define( 'FTP_HOST', '' );

// Absolute path to the location of data files to import.
// Must be readable by the web server.
define( 'LOCAL_DATA_PATH', '/home/mlarson' );
//define( 'LOCAL_DATA_PATH', '/Applications/XAMPP/xamppfiles/htdocs/vrdata' );

// Relative to the site dir
// Must be readable and writable by the web server.
define( 'DATA_DEST_DIR', 'data' );

// Whether or not to remove categories and products that 
// do not appear in the new data file.
define( 'REMOVE_MISSING_DATA', true );