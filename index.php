<?php

require_once( 'includes/shared.php' );

if ( empty($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] == '/' )
	die("Nothing to see here. Please move along.");
	// todo: add option for listing active experiments

// parse URL
$experiment_name = preg_replace( '!^' . APPPATH . '(.*?)/?$!', '$1', $_SERVER['REQUEST_URI'] );

if ( defined('ENABLE_TEST') && ENABLE_TEST && $experiment_name == 'index.php' )
	print_test_page();

if ( !experiment_files_exist( $experiment_name ) )
	die( 'Experiment not found! Please check your URL.' );

$experiment = experiment_metadata( $experiment_name );
if ( $experiment['status'] !== 'active' )
	die( 'Experiment is not active.' );

$csv_file = 'data/' . $experiment_name . '.csv';
$template_file = 'data/' . $experiment_name . '.html';

// now we have an experiment.
// get the active list number:
// note that list_number() may have to set a cookie, so no output should occur before this point.
$list_number = list_number( $experiment_name );
// todo later: setting for whether multiple lists should be allowed?

// save submission
// todo: make sure multiple submissions with the same assignmentId is denied
if ( isset($_POST['assignmentId']) ) {
	$data = array();
	$data['WorkerId'] = $cookie['workderid'];
	$data['AssignmentId'] = $_POST['assignmentId'];
	// todo: other fields from turk?

	foreach ( read_data( $experiment_name, $list_number ) as $key => $value ) {
		$data['Input.' . $key] = $value;
	}

	foreach ( $_POST as $key => $value ) {
		if ( $key == 'assignmentId' )
			continue;
		$data['Answer.' . $key] = $value;
	}
	
	record_results( $experiment_name, $data );

	echo '<html><head><title>' . $experiment['thanks'] . '</title></head><body>' . $experiment['thanks'] . '</body></html>';
	exit;
}
	
// print survey
$html = construct_experiment( $experiment_name, $list_number );

echo '<html><head><title>' . $experiment['title'] . '</title><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/></head><body>';

echo '<form name="mturk_form" method="post" id="mturk_form" action="' . APPURL . $experiment_name . '"><input type="hidden" value="' . new_id() . '" name="assignmentId" id="assignmentId" />';

echo $html;

echo '</form></body></html>';
