<?php

global $cookie, $experiment_name, $experiment;

// todo: make sure multiple submissions with the same assignmentId is denied

// todo: read this from the form instead of the cookie:
if ( !isset($_POST['turkserver']) ||
	!isset($_POST['turkserver']['workerid']) ||
	!isset($_POST['turkserver']['list_number']) )
	die( "Error: improper submission." );

$cookie_reliable = $_POST['turkserver']['workerid'] == $cookie['workerid'];
$list_number = $_POST['turkserver']['list_number'];

$data = array(
	'HITId' => "{$experiment_name}-{$list_number}",
	'HITTypeId' => "{$experiment_name}",
	'Title' => $experiment['title'],
	'AssignmentId' => $_POST['assignmentId'],
	'WorkerId' => $_POST['turkserver']['workerid'],
	'AssignmentStatus' => 'Submitted',
	'TurkServerCookieReliable' => ( $cookie_reliable ? 1 : 0 ),
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
	if ( $key == 'assignmentId' || $key == 'turkserver' )
		continue;
	$data['Answer.' . $key] = $value;
}

record_results( $experiment_name, $data );

?><html>
<head>
<title><?php echo $experiment['thanks']; ?></title>
</head>
<body>

<?php echo $experiment['thanks'] ?>

</body>
</html>
<?php exit; ?>