<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<base href="{$module_root}"/>
		<title>DASe Installation & Configuration</title>
		<link rel="stylesheet" type="text/css" href="{$module_root}/css/style.css">
		<script type="text/javascript" src="{$app_root}www/scripts/dase.js"></script> 
		<script type="text/javascript" src="{$app_root}www/scripts/dase/form.js"></script> 
		<script type="text/javascript" src="scripts/install.js"></script> 
		<script type="text/javascript" src="scripts/jquery.js"></script> 
	</head>
	<body>
		<div class="container">
			<div class="branding">
				DASe Installation & Configuration
			</div>
			<div class="content">
				<h1>Database Settings</h1>
				<div id="msg"></div>
				<form id="db_form" action="dbchecker" method="post">
					<label for="db_type">Database Type</label>
					<select name="db_type">
						<option {if 'mysql' == $conf.db.type}selected="selected"{/if} value="mysql">MySQL</option>
						<option {if 'pgsql' == $conf.db.type}selected="selected"{/if} value="pgsql">PostgreSQL</option>
						<option {if 'sqlite' == $conf.db.type}selected="selected"{/if} value="sqlite">SQLite</option>
					</select>
					<p>
					<label for="db_name">Database Host</label>
					<input type="text" name="db_host" value="{$conf.db.host}"/>
					</p>

					<p>
					<label for="db_name">Database Name</label>
					<input type="text" name="db_name" value="{$conf.db.name}"/>
					</p>

					<p>
					<label for="db_name">Database User</label>
					<input type="text" name="db_user" value="{$conf.db.user}"/>
					</p>

					<p>
					<label for="db_name">Database Password</label>
					<input type="text" name="db_pass" value="{$conf.db.pass}"/>
					</p>
					<p>
					<label for="db_name">Database Path (SQLite only)</label>
					<input type="text" name="db_path" value="{$conf.db.path}"/>
					</p>
					<p>
					<input type="submit" value="check settings"/>
					<input type="submit" value="save settings"/>
					</p>
				</form>

			</div>



		</div>
	</body>
</html>
