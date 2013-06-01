<?php

function config_health() {
	if ( !file_exists('config.php') ) {
		if ( !copy('config.sample.php', 'config.php') )
			return 'The config file <code>config.php</code> could not be created. Please copy the sample config file <code>config.sample.php</code> to <code>config.php</code>.';
	}
	return true;
}

function server_health() {
	if ( !version_compare(phpversion(), '5.3', '>=') )
		return 'PHP 5.3 or later is required!';

	# These flags are set in htaccess
	if ( !array_key_exists('HTTP_READ_HTACCESS', $_SERVER) )
		return 'The .htaccess file is not being read!';
	if ( !array_key_exists('HTTP_MOD_REWRITE', $_SERVER) )
		return 'Apache mod_rewrite is required!';
	
	return true;
}

function experiment_meta_health() {
	if ( !file_exists( EXPERIMENTS_META_FILE ) ) {
		touch( EXPERIMENTS_META_FILE );
		if ( !file_exists( EXPERIMENTS_META_FILE ) )
			return 'Experiment meta file could not be created!';
	}
	
	if ( !is_readable( EXPERIMENTS_META_FILE ) )
		return 'Experiment meta file could not be read!';
	if ( !is_writable( EXPERIMENTS_META_FILE ) )
		return 'Experiment meta file cannot be written to!';
	
	$data = parse_ini_file( EXPERIMENTS_META_FILE, true );
	if ( is_array($data) === false )
		return 'Experiment meta file could not be read as an INI file!';
	
	return true;
}

function data_health() {
	if ( !is_dir('data') )
		return 'Data directory does not exist!';
	if ( !is_writable('data') )
		return 'Data directory cannot be written to!';
	return true;
}

function cookie_health() {
	global $cookie;
	
	if ( !isset($_COOKIE) || !is_array($_COOKIE) )
		return '$_COOKIE superglobal could not be read!';
	if ( !isset($_COOKIE[COOKIE_NAME]) || !isset($_COOKIE[COOKIE_NAME]) )
		return 'Cookies saving could not be verified! Please refresh the page.';
	if ( $_COOKIE[COOKIE_NAME]['workerid'] !== $cookie['workerid'] )
		return 'Cookies are not being saved correctly! This may be a browser issue.';

	return true;
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
	
	$meta_health = experiment_meta_health();
	if ( $meta_health !== true )
		die( "Error: " . $meta_health );
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
				$data[] = $keyed_row_data;
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

function test_page() {
	require('includes/test.php');
	exit;
}

function save_page( $experiment_name, $experiment ) {
	global $cookie;

	// todo: make sure multiple submissions with the same assignmentId is denied

	// todo: read this from the form instead of the cookie:
	$list_number = list_number( $experiment_name );
	
	$data = array(
		'HITId' => "{$experiment_name}-{$list_number}",
		'HITTypeId' => "{$experiment_name}",
		'Title' => $experiment['title'],
		'AssignmentId' => $_POST['assignmentId'],
		'WorkerId' => $cookie['workerid'],
		'AssignmentStatus' => 'Submitted',
	);
	$extra_turk_fields = array(
		'Description', 'Keywords', 'Reward', 'CreationTime', 'MaxAssignments', 'RequesterAnnotation', 'AssignmentDurationInSeconds', 'AutoApprovalDelayInSeconds', 'Expiration', 'NumberOfSimilarHITs', 'LifetimeInSeconds', 'AcceptTime', 'SubmitTime', 'AutoApprovalTime', 'ApprovalTime', 'RejectionTime', 'RequesterFeedback', 'WorkTimeInSeconds', 'LifetimeApprovalRate', 'Last30DaysApprovalRate', 'Last7DaysApprovalRate'
	);
	if ( isset($experiment['add_extra_turk_fields']) && 
		( $experiment['add_extra_turk_fields'] == 'true' || $experiment['add_extra_turk_fields'] == true ) ) {
		foreach ( $extra_turk_fields as $field ) {
			$data[$field] = '';
		}
	}

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