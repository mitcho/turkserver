<?php

require_once( 'includes/shared.php' );

if ( empty($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] == '/' )
	die( "Nothing to see here. Please move along." );
	// todo: add option for listing active experiments

// parse URL
$experiment_name = preg_replace( '!^' . APPPATH . '/?(.*?)/?$!', '$1', $_SERVER['REQUEST_URI'] );

// display test page:
if ( defined('ENABLE_TEST') && ENABLE_TEST && $experiment_name == 'index.php' )
	test_page();

if ( !experiment_files_exist( $experiment_name ) )
	die( 'Experiment not found! Please check your URL.' );

$experiment = experiment_metadata( $experiment_name );
if ( $experiment['status'] !== 'active' )
	die( 'Experiment is not active.' );

// save submission:
if ( isset($_POST['assignmentId']) )
	require( 'includes/save.php' );

// Now we're going to display an experiment.
	
require( 'includes/experiment.php' );
