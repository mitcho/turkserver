<?php

function new_experiment_id() {
	global $db;

	$unique = false;
	while ( !$unique ) {
		$id = uniqid(); // returns 13 digit alphanumeric id
	
		$db->query( "select * from experiments where hash = '" . $db->real_escape_string($id) . "'" );
		if ( !$db->use_result() ) {
			$unique = true;
		}
	}
	// danger of infinite loop. todo: limit to some number of tries?
	
	return $id;
}

function new_worker_id() {
	// todo cookie workers?
	return uniqid('turkserver');
}

function new_assignment_id() {
	return uniqid('turkserver');
}

function construct_experiment( $filename, $list ) {
	if ( !file_exists( APPDIR . '/data/' . $filename . '.html' ) ||
		!is_readable( APPDIR . '/data/' . $filename . '.html' ) )
		die( "Error: template file could not be loaded." );
	$template = file_get_contents( APPDIR . '/data/' . $filename . '.html' );
	
	$fields = read_data( $filename, $list );

	$template_fields = preg_replace( '!^.*$!', '\\${$0}', array_keys($fields) );
	$html = str_replace( $template_fields, array_values($fields), $template );
	$html .= '<p><input type="submit" id="submitButton" value="Submit" /></p>';
	
	// used by MTurk:
	// $html .= "<script type='text/javascript' src='https://s3.amazonaws.com/mturk-public/externalHIT_v1.js'></script>";
	// $html .= '<script language="Javascript">turkSetAssignmentID();</script>';

	return $html;
}

function read_data( $filename, $list = false ) {
	if ( !file_exists( APPDIR . '/data/' . $filename . '.csv' ) ||
		!is_readable( APPDIR . '/data/' . $filename . '.csv' ) )
		die( "Error: data file could not be loaded." );
	$csv = file( APPDIR . '/data/' . $filename . '.csv' );

	$data = array();
	$keys = false;
	foreach ( $csv as $n => $row ) {
		if ( empty($row) )
			continue;
		$row_data = str_getcsv( $row, ',', '"', '\\' );
		if ( !$keys )
			$keys = $row_data;
		else
			$data[] = array_combine( $keys, $row_data );
	}

	if ( $list !== false ) {
		if ( !isset($data[$list]) )
			die("Error: list cannot be found in data.");

		return $data[$list];
	}

	return $data;
}

function record_results( $filename, $data ) {
	$results = fopen( APPDIR . '/data/' . $filename . '.results.csv', 'a+' );
	
	// if this is a new file, add the field names first:
	$stat = fstat($results);
	if ( $stat['size'] == 0 )
		fputcsv( $results, array_keys( $data ) );
	
	// todo: validate that the header row and the keys are the same
	// ... the question is, what happens if it doesn't match?
	fputcsv( $results, $data );
	fclose( $results );
}