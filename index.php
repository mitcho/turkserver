<?php

require_once( 'includes/shared.php' );

if ( empty($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] == '/' )
	die("Nothing to see here. Please move along.");
	// todo: add option for listing active experiments

// parse URL
$experiment_name = preg_replace( '!^' . APPPATH . '(.*?)/?$!', '$1', $_SERVER['REQUEST_URI'] );

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
	save_page( $experiment_name, $experiment );

// Now we're going to display an experiment.

// get the active list number:
// note that list_number() may have to set a cookie, so no output should occur before this point.
$list_number = list_number( $experiment_name );
	
echo '<html><head><title>' . $experiment['title'] . '</title><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/></head><body>';

echo '<form name="mturk_form" method="post" id="mturk_form" action="' . APPURL . $experiment_name . '"><input type="hidden" value="' . new_id() . '" name="assignmentId" id="assignmentId" />';

echo construct_experiment( $experiment_name, $list_number );

echo '</form></body></html>';
