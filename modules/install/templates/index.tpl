<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<base href="{$module_root}"/>
		<title>DASe Installation & Configuration</title>
		<link rel="stylesheet" type="text/css" href="{$module_root}/css/style.css">
		<script type="text/javascript" src="{$app_root}www/scripts/dase.js"></script> 
		<script type="text/javascript" src="{$app_root}www/scripts/dase/form.js"></script> 
		<script type="text/javascript" src="scripts/install.js"></script> 
	</head>
	<body>
		<div class="container">
			<div class="branding">
				DASe Installation & Configuration
			</div>
			<div class="content">
				<form id="check_form" action="dbchecker" method="post">
					<h1>Archive Information</h1>
					<label for="main_title">Archive Title</label>
					<input type="text" name="main_title" value="{$conf.main_title}"/>
					<h1>Admin User Information</h1>
					<label for="eid">Username</label>
					<input type="text" name="eid" value="{$eid}"/>
					<label for="password">Password</label>
					<input type="text" name="password" value="{$password}"/>
					<h1>Media Repository Settings</h1>
					<label for="path_to_media">Path to Media Directory (must be writable by web server)</label>
					<input type="text" size="40" name="path_to_media" value="{$conf.path_to_media}"/>
					<span id="path_to_media_msg"></span>
					<input type="submit" id="repos_check_button" value="check directory permissions"/>
					<h1>ImageMagick</h1>
					<label for="convert_path">Path to ImageMagick "convert" Utility</label>
					<input type="text" name="convert_path" value="{$convert_path}"/>
					<h1>Database Settings</h1>
					<label for="table_prefix">Table Prefix (optional)</label>
					<input type="text" name="table_prefix" value="{$conf.table_prefix}"/>
					<label for="db_type">Database Type</label>
					<select name="db_type">
						<option {if 'mysql' == $conf.db.type}selected="selected"{/if} value="mysql">MySQL</option>
						<option {if 'pgsql' == $conf.db.type}selected="selected"{/if} value="pgsql">PostgreSQL</option>
						<option {if 'sqlite' == $conf.db.type}selected="selected"{/if} value="sqlite">SQLite</option>
					</select>
					<label for="db_name">Database Host</label>
					<input type="text" name="db_host" value="{$conf.db.host}"/>
					<label for="db_name">Database Name</label>
					<input type="text" name="db_name" value="{$conf.db.name}"/>
					<label for="db_name">Database User</label>
					<input type="text" name="db_user" value="{$conf.db.user}"/>
					<label for="db_name">Database Password</label>
					<input type="text" name="db_pass" value="{$conf.db.pass}"/>
					<label for="db_name">Database Path (SQLite only)</label>
					<input type="text" name="db_path" value="{$conf.db.path}"/>
					<input type="submit" id="db_check_button" value="check database settings"/>
					<span id="db_msg"></span>
					<input type="submit" id="save_settings_button" value="save settings"/>
					<input type="submit" id="init_db_button" class="hide" value="initialize database"/>
					<div id="init_db_msg"></div>
					<input type="submit" id="setup_db_button" class="hide" value="create admin user and sample collection"/>
					<textarea id="local_config_txt" class="hide" cols="80" rows="28" name="local_config"></textarea>
				</form>
			</div>
		</div>
	</body>
</html>
