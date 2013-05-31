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

/* Advanced settings */
define( 'DEBUG', false ); // set to true for debug mode
define( 'EXPERIMENTS_META_FILE', 'experiments_meta.ini' ); // file name for experiment metadata

// define( 'APPURL', 'http://REPLACE SERVER NAME/REPLACE PATH/turkserver/' ); // with slash!