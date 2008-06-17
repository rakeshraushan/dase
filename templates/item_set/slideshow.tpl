<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title>
		 slideshow	
		</title>
		<base href="{$app_root}"/>

		<link rel="shortcut icon" href="www/images/favicon.ico"/>

		<script type="text/javascript">
			var _slideshow = {literal}{{/literal}
				'url':'{$json_url}',
					'user':'{$eid}',
					'pass':'{$http_pw}',
					{literal}}{/literal}
		</script>
		<script type="text/javascript" src="www/scripts/dase_slideshow/jquery.js"></script>
		<script type="text/javascript" src="www/scripts/dase_slideshow/ui.core.js"></script>
		<script type="text/javascript" src="www/scripts/dase_slideshow/ui.draggable.js"></script>
		<script type="text/javascript" src="www/scripts/dase_slideshow/dase_slideshow.js"></script>
	</head>

	<body>
	</body>
</html>


