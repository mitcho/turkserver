<?php

define( 'APPDIR', dirname($_SERVER['SCRIPT_FILENAME']) ); // no slash at end

if ( !file_exists( APPDIR . '/config.php' ) ) {
	if ( !is_readable( APPDIR . '/config.sample.php' ) )
		die( "Error: sample config file could not be read!" );
	require_once( APPDIR . '/config.sample.php' );
} else if ( !is_readable( APPDIR . '/config.php' ) ) {
	die( "Error: config file could not be read!" );
} else {
	require_once( APPDIR . '/config.php' );
}

if ( defined( 'DEBUG' ) && DEBUG ) {
	error_reporting(E_ALL);
	ini_set('display_errors', true);	
}

require_once( APPDIR . '/includes/functions.php' );

set_global_cookie();