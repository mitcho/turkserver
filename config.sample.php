<?php
// Sample config file
//
// Do not modify config.sample.php. Instead, copy to config.php and modify config.php.
// If config.php exists, settings are read from config.php, not config.sample.php.

/* Experiment defaults */
$defaults = array(
	'status' => 'active', // default state for experiments
	'title' => 'Survey',
	'thanks' => 'Thank you!',
);

// SETTINGS BELOW ARE ADVANCED SETTINGS! THEY PROBABLY DON'T NEED TO BE CHANGED.

/* Advanced settings */
define( 'DEBUG', false ); // set to true for debug mode
define( 'ENABLE_TEST', true ); // set to false to disable the test page at /test
define( 'EXPERIMENTS_META_FILE', 'experiments_meta.ini' ); // file name for experiment metadata

/* Environment settings */
define( 'APPURL', dirname($_SERVER['SCRIPT_NAME']) ); // with trailing slash
define( 'APPPATH', dirname($_SERVER['SCRIPT_NAME']) ); // with trailing slash

/* Cookie settings */
define( 'COOKIE_NAME', 'turkserver' );
define( 'COOKIE_EXPIRE', time() + 60*60*24*365 ); // default: 1 year cookie
define( 'COOKIE_PATH', dirname($_SERVER['SCRIPT_NAME']) );
define( 'COOKIE_DOMAIN', $_SERVER['SERVER_NAME'] );
