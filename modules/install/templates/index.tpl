<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<base href="{$module_root}/"/>
<title>DASe Installation & Configuration</title>
<link rel="stylesheet" type="text/css" href="{$module_root}/css/style.css">
		<script type="text/javascript" src="scripts/jquery.js"></script> 
</head>
<body>
<div class="container">
<div class="branding">
DASe Installation & Configuration
</div>
<div class="content">

	<form>
		<select name="dbtype">
			<option value="mysql">MySQL</option>
			<option value="pgsql">PostgreSQL</option>
			<option value="sqlite">SQLite</option>
		</select>
		{foreach key=k item=c from=$conf.db}
		<p>
		<label>{$k}</label>
		</p>
		{/foreach}

</div>



</div>
</body>
</html>
