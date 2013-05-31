<?php

function print_test_page() {
	global $cookie;
	// todo: check for $_COOKIE global
	// Tell your server administrator (hosting provider) to allow the $_COOKIE superglobal by changing <a href='http://www.php.net/manual/en/ini.core.php#ini.variables-order'>PHP's variables_order setting</a>.
}

function new_id() {
	return uniqid('turkserver');
}

function experiment_files_exist( $experiment ) {
	return 
		file_exists('data/' . $experiment . '.csv') &&
		is_readable('data/' . $experiment . '.csv') &&
		file_exists('data/' . $experiment . '.html') &&
		is_readable('data/' . $experiment . '.html');
}
function experiment_metadata( $experiment = false ) {
	global $defaults;
	
	if ( !file_exists( EXPERIMENTS_META_FILE ) ) {
		touch( EXPERIMENTS_META_FILE );
		if ( !file_exists( EXPERIMENTS_META_FILE ) )
			die( 'Experiment meta file could not be created!' );
	}
	
	if ( !is_readable( EXPERIMENTS_META_FILE ) )
		die( 'Experiment meta file could not be read!' );
	
	$data = parse_ini_file( EXPERIMENTS_META_FILE, true );
	
	if ( $experiment === false )
		return $data;
	
	if ( !isset($data[$experiment]) ) {
		$new_entry = "[{$experiment}]\n" .
			"status = \"{$defaults['status']}\"\n" .
			"title = \"{$defaults['title']}\"\n" . 
			"thanks = \"{$defaults['thanks']}\"\n" .
			"\n";
		file_put_contents( EXPERIMENTS_META_FILE, $new_entry, FILE_APPEND | LOCK_EX );
		return $defaults;
	}
	
	return $data[$experiment];
}

function construct_experiment( $experiment_name, $list_number ) {
	if ( !file_exists( APPDIR . '/data/' . $experiment_name . '.html' ) ||
		!is_readable( APPDIR . '/data/' . $experiment_name . '.html' ) )
		die( "Error: template file could not be loaded." );
	$template = file_get_contents( APPDIR . '/data/' . $experiment_name . '.html' );
	
	$fields = read_data( $experiment_name, $list_number );

	$template_fields = preg_replace( '!^.*$!', '\\${$0}', array_keys($fields) );
	$html = str_replace( $template_fields, array_values($fields), $template );
	$html .= '<p><input type="submit" id="submitButton" value="Submit" /></p>';
	
	// used by MTurk:
	// $html .= "<script type='text/javascript' src='https://s3.amazonaws.com/mturk-public/externalHIT_v1.js'></script>";
	// $html .= '<script language="Javascript">turkSetAssignmentID();</script>';

	return $html;
}

function set_global_cookie() {
	global $cookie;

	if ( !isset($_COOKIE) )
		die( "Error: COOKIE superglobal could not be read." );
	if ( isset($_COOKIE[COOKIE_NAME]) )
		$cookie = $_COOKIE[COOKIE_NAME];
	else
		$cookie = array();		
	
	if ( !isset($cookie['workerid']) ) {
		$id = new_id();
		setcookie(COOKIE_NAME . "[workerid]", $id, COOKIE_EXPIRE, COOKIE_PATH, COOKIE_DOMAIN);
		$cookie['workerid'] = $id;
	}
	
	if ( !isset($cookie['experiments']) || !is_array($cookie['experiments']) )
		$cookie['experiments'] = array();
}

function list_number( $experiment_name ) {
	global $cookie;
		
	// if the cookie specifies a list, return that:
	if ( isset($cookie['experiments'][$experiment_name]) )
		return $cookie['experiments'][$experiment_name];

	// pick a random list number:
	$list_numbers = array_keys(read_data( $experiment_name ));
	$index = rand(0, count($list_numbers) - 1);
	$list_number = $list_numbers[$index];

	setcookie(COOKIE_NAME . "[experiments][{$experiment_name}]", $list_number,
		COOKIE_EXPIRE, COOKIE_PATH, COOKIE_DOMAIN);
	$cookie['experiments'][$experiment_name] = $list_number;

	return $list_number;
}

function random_list( $experiment_name ) {
	$list_numbers = array_keys(read_data( $experiment_name ));
	$index = rand(0, count($list_numbers) - 1);
	return $list_numbers[$index];
}

function read_data( $experiment_name, $list_number = false ) {
	static $data_cache;
	if ( !isset($data_cache) )
		$data_cache = array();

	// if not cached:
	if ( !isset($data_cache[$experiment_name]) ) {
		if ( !file_exists( APPDIR . '/data/' . $experiment_name . '.csv' ) ||
			!is_readable( APPDIR . '/data/' . $experiment_name . '.csv' ) )
			die( "Error: data file could not be loaded." );
		$csv = file( APPDIR . '/data/' . $experiment_name . '.csv' );

		$data = array();
		$keys = false;
		foreach ( $csv as $n => $row ) {
			if ( empty($row) )
				continue;
			$row_data = str_getcsv( $row, ',', '"', '\\' );
			if ( !$keys ) {
				$keys = $row_data;
			} else {
				$keyed_row_data = array_combine( $keys, $row_data );
				if ( !isset($keyed_row_data['list']) )
					die( "Error: data file does not have a list column." );
				$this_list_number = intval($keyed_row_data['list']);
				$keyed_row_data['list'] = $this_list_number;
				if ( isset($data[$this_list_number]) )
					die( "Error: the data file has multiple list #{$this_list_number}s" );
				$data[$this_list_number] = $keyed_row_data;
			}
		}

		$data_cache[$experiment_name] = $data;
	}
	$data = $data_cache[$experiment_name];

	if ( $list_number !== false ) {
		if ( !isset($data[$list_number]) )
			die("Error: list {$list_number} cannot be found in data.");

		return $data[$list_number];
	}

	return $data;
}

function record_results( $experiment_name, $data ) {
	$filename = APPDIR . '/data/' . $experiment_name . '.results.csv';

	if ( !file_exists($filename) ) {
		touch( APPDIR . '/data/' . $experiment_name . '.results.csv' );
		if ( !file_exists($filename) )
			die("Results file could not be created!");
	}

	$results = fopen( $filename, 'a+' );
	
	// if this is a new file, add the field names first:
	$stat = fstat($results);
	if ( $stat['size'] == 0 )
		fputcsv( $results, array_keys( $data ) );
	
	// todo: validate that the header row and the keys are the same
	// ... the question is, what happens if it doesn't match?
	fputcsv( $results, $data );
	fclose( $results );
}