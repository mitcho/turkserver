<?php

define( 'APPDIR', dirname($_SERVER['SCRIPT_FILENAME']) ); // no slash at end

if ( !file_exists( APPDIR . '/config.php' ) || !is_readable( APPDIR . '/config.php' ) )
	exit("<h1>Error: Config file not found.</h1>");
require_once( APPDIR . '/config.php' );

// set DEBUG flag
if ( defined( 'DEBUG' ) && DEBUG ) {
	error_reporting(E_ALL);
	ini_set('display_errors', true);	
} else {
	define( 'DEBUG', false );
}

// set APPURL
if ( !defined( 'APPURL' ) )
	define( 'APPURL', dirname($_SERVER['SCRIPT_NAME']) );
// set APPPATH
if ( !defined( 'APPPATH' ) )
	define( 'APPPATH', dirname($_SERVER['SCRIPT_NAME']) );

// connect to MySQL
$db = new mysqli( $db_config['host'], $db_config['user'], $db_config['pass'], $db_config['database'] );
if ( $db->connect_error ) {
	die('Database error: (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
$db->set_charset( 'utf8' );

require_once( APPDIR . '/includes/functions.php' );
