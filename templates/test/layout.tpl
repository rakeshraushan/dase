<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title>
			{block name="title"}DASe: Digital Archive Services{/block}
		</title>
		<meta name="description" content="
		The Digital Archive Services project 
		is a lightweight digital content repository
		created by the College of Liberal Arts at 
		The University of Texas at Austin."/>

		<base href="{$app_root}"/>

		<link rel="stylesheet" type="text/css" href="css/error-test.css"/>

		<script type="text/javascript" src="scripts/http.js"></script>
		<script type="text/javascript" src="scripts/json2.js"></script>
		<script type="text/javascript" src="scripts/dase.js"></script>

	</head>

	<body>
		<div id="container">
			{block name="test-data"}default text{/block}
			<insert-test-data/>
		</div><!-- closes id=container-->
	</body>
</html>
