<?php

require_once( 'includes/shared.php' );

if ( empty($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] == '/' )
	die("Nothing to see here. Please move along.");

// parse URL
$path = preg_replace( '!^' . APPPATH . '(.*?)/?$!', '$1', $_SERVER['REQUEST_URI'] );


// look for matching experiment
$result = $db->query( "select * from experiments where hash = '" . $db->real_escape_string($path) . "' limit 1" );
$row = $result->fetch_object();
if ( !$row )
	die("Survey not found.");


// if the list is empty, redirect to an individual list URL
if ( empty($row->list) ) {
	$result = $db->query( "select * from experiments where filename = '" . $db->real_escape_string($row->filename) . "' and list is not null order by rand() limit 1" );
	
	$row = $result->fetch_object();
	if ( !$row )
		die( "Error: List could not be chosen." );
	
	header( 'Location: ' . APPURL . $row->hash );
	
	exit;
}


// save submission
// todo: make sure multiple submissions with the same assignmentId is denied
if ( isset($_POST['assignmentId']) ) {
	$data = array();
	$data['WorkerId'] = new_worker_id();
	$data['AssignmentId'] = $_POST['assignmentId'];
	// todo: other fields from turk?

	foreach ( read_data( $row->filename, $row->list ) as $key => $value ) {
		$data['Input.' . $key] = $value;
	}

	foreach ( $_POST as $key => $value ) {
		if ( $key == 'assignmentId' )
			continue;
		$data['Answer.' . $key] = $value;
	}
	
	record_results( $row->filename, $data );

	echo '<html><head><title>' . TITLE . '</title></head><body>' . THANKS . '</body>';
	exit;
}
	

// print survey
$html = construct_experiment( $row->filename, $row->list );

// todo: make TITLE include some title info from the db
echo '<html><head><title>' . TITLE . '</title><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/></head><body>';

echo '<form name="mturk_form" method="post" id="mturk_form" action="' . APPURL . $row->hash . '"><input type="hidden" value="' . new_assignment_id() . '" name="assignmentId" id="assignmentId" />';

echo $html;

echo '</form></body></html>';
