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
				<form id="check_form" action="dbchecker" method="post">
				<h1>Admin User Information</h1>
					<table class="form_table">
						<tr>
							<th>
								<label for="eid">Username</label>
							</th>
							<td>
								<input type="text" name="eid" value="{$eid}"/>
							</td>
						</tr>
						<tr>
							<th>
								<label for="password">Password</label>
							</th>
							<td>
								<input type="text" name="password" value="{$password}"/>
							</td>
						</tr>
					</table>
				<h1>Media Repository Settings</h1>
					<table class="form_table">
						<tr>
							<th>
								<label for="path_to_media">Path to Media Directory (must be writable by web server)</label>
							</th>
							<td>
								<input type="text" size="40" name="path_to_media" value="{$conf.path_to_media}"/>
								<span id="path_to_media_msg"></span>
							</td>
						</tr>
						<tr>
							<th>
								<label for="graveyard">Path to Graveyard (deleted item metadata archive)</label>
							</th>
							<td>
								<input type="text" size="40" name="graveyard" value="{$conf.graveyard}"/>
								<span id="graveyard_msg"></span>
							</td>
						</tr>
						<tr>
							<th></th>
							<td>
								<input type="submit" id="repos_check_button" value="check directory permissions"/>
							</td>
						</tr>
					</table>
				<h1>Database Settings</h1>
					<table class="form_table">
						<tr>
							<th>
								<label for="db_type">Database Type</label>
							</th>
							<td>
								<select name="db_type">
									<option {if 'mysql' == $conf.db.type}selected="selected"{/if} value="mysql">MySQL</option>
									<option {if 'pgsql' == $conf.db.type}selected="selected"{/if} value="pgsql">PostgreSQL</option>
									<option {if 'sqlite' == $conf.db.type}selected="selected"{/if} value="sqlite">SQLite</option>
								</select>
							</td>
						</tr>
						<tr>
							<th>
								<label for="db_name">Database Host</label>
							</th>
							<td>
								<input type="text" name="db_host" value="{$conf.db.host}"/>
							</td>
						</tr>
						<tr>
							<th>
								<label for="db_name">Database Name</label>
							</th>
							<td>
								<input type="text" name="db_name" value="{$conf.db.name}"/>
							</td>
						</tr>
						<tr>
							<th>
								<label for="db_name">Database User</label>
							</th>
							<td>
								<input type="text" name="db_user" value="{$conf.db.user}"/>
							</td>
						</tr>
						<tr>
							<th>
								<label for="db_name">Database Password</label>
							</th>
							<td>
								<input type="text" name="db_pass" value="{$conf.db.pass}"/>
							</td>
						</tr>
						<tr id="db_path">
							<th>
								<label for="db_name">Database Path (SQLite only)</label>
							</th>
							<td>
								<input type="text" name="db_path" value="{$conf.db.path}"/>
							</td>
						</tr>
						<tr>
							<th></th>
							<td>
								<input type="submit" id="db_check_button" value="check database settings"/>
								<span id="db_msg"></span>
							</td>
						</tr>
						<tr id="init_db" class="hide">
							<th></th>
							<td>
								<input type="submit" id="save_settings_button" value="save settings"/>
								<input type="submit" id="init_db_button" class="hide" value="initialize database"/>
								<div id="init_db_msg"></div>
								<textarea id="local_config_txt" class="hide" cols="80" rows="16" name="local_config"></textarea>
							</td>
						</tr>
						<tr id="completed" class="hide">
							<th></th>
							<td>
								<a href="{$app_root}login/form">please login</a>
							</td>
						</tr>
					</table>

				</form>

			</div>



		</div>
	</body>
</html>
