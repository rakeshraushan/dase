<?php
if (isset($params[0])) {
	switch ($params[0]) {
	case 'standard':
		$file = 'standard.log';
		break;
	case 'error':
		$file = 'error.log';
		break;
	case 'sql':
		$file = 'sql.log';
		break;
	case 'remote':
		$file = 'remote.log';
		break;
	default:
		header("HTTP/1.0 404 Not Found");
		exit;
	}
	$logfile = file_get_contents(DASE_PATH . "/log/" . $file);
	echo "<html><body><pre>$logfile</pre></body></html>";
}
exit;
