<?php
// Sample config file
//
// Do not modify config.sample.php. Instead, copy to config.php and modify config.php
// Settings are read from config.php, not config.sample.php

// Database configuration
$db_config = array(
	'host' => 'localhost',
	'user' => 'ENTER USERNAME HERE',
	'pass' => 'ENTER PASSWORD HERE',
	'database' => 'ENTER DATABASE NAME HERE'
);

// define( 'APPURL', 'http://REPLACE SERVER NAME/REPLACE PATH/turkserver/' ); // with slash!

// DEBUG: set to true for debug mode. This will print errors.
define( 'DEBUG', false );

// TITLE: this title is the title of every survey
define( 'TITLE', 'Survey' );

// THANKS: this text is printed after a survey is submitted
define( 'THANKS', 'Thank you!' );
