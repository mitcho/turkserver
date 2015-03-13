<?php

if ( !defined('APPDIR') )
	die();

?><html><head><title>turkserver test page</title>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
<style>
.entry { height: 30px; vertical-align: middle; padding: 5px; }
.icon { width: 30px; height: 30px; background-repeat: no-repeat; float: left; padding-right: 10px; margin-top: -7px; }
.icon.good { background-image: url(<?php echo rtrim(APPPATH,'/'); ?>/includes/good.png) }
.icon.bad { background-image: url(<?php echo rtrim(APPPATH,'/'); ?>/includes/bad.png) }
</style>
</head><body>
<h1>turkserver test page</h1>
<?php

$healths = array(
	'Config file' => config_health(),
	'Server' => server_health(),
	'Experiment meta file' => experiment_meta_health(),
	'Data directory' => data_health(),
	'Cookies' => cookie_health(),
);

$healthy = true;
foreach ( $healths as $type => $health ) {
	if ( $health === true ) {
		echo "<div class='entry'><div class='icon good'>&nbsp;</div> {$type} ok</div>";
	} else {
		echo "<div class='entry'><div class='icon bad'>&nbsp;</div> {$health}</div>";
		$healthy = false;
	}
}

if ( $healthy ) {
	echo "<p>You're all set! We suggest you now turn off this test page feature, by editing <code>config.php</code> and setting <code>ENABLE_TEST</code> to false.</p>";
} else {
	echo "<p>Please resolve these issues before moving on to host experiments.</p>";
}

?>
</body><html>