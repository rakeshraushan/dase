<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title>jqtest</title>
		<style type="text/css"> </style>
		<base href="{$app_root}"/>

		<script src="http://www.google.com/jsapi"></script>
		<script>
			{literal}
			// Load jQuery
			google.load("jquery", "1");
			// on page load complete, fire off a jQuery json-p query
			// against Google web search
			google.setOnLoadCallback(function() {
				$.getJSON("http://quickdraw.laits.utexas.edu/dase1/tag/2850?format=json",
				// on search completion, process the results
				function (data) {
					for (var i in data) {
						alert(i);
					}
				});
			});
			{/literal}
		</script>

	</head>

	<body>
		<h1>test</h1>

	</body>
</html>
