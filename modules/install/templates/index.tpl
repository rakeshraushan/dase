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
					<table id="formTable">
						<tr>
							<th>
								<label for="main_title">DASe Archive Title</label>
							</th>
							<td>
								<input type="text" name="main_title" value="{$conf.main_title}"/>
							</td>
						</tr>
						<tr>
							<th>
								<label for="eid">Admin Username</label>
							</th>
							<td>
								<input type="text" name="eid" value="{$eid}"/>
							</td>
						</tr>
						<tr>
							<th>
								<label for="password">Admin Password</label>
							</th>
							<td>
								<input type="text" name="password" value="{$password}"/>
							</td>
						</tr>
						<tr>
							<th>
								<label for="path_to_media">Path to Media Directory (must be writable by web server)</label>
							</th>
							<td>
								<input type="text" size="40" name="path_to_media" value="{$conf.path_to_media}"/>
								<span id="path_to_media_msg"></span>
								<input type="submit" id="repos_check_button" value="check directory permissions"/>
							</td>
						</tr>
						<tr>
							<th>
								<label for="convert_path">Path to ImageMagick "convert" Utility</label>
							</th>
							<td>
								<input type="text" name="convert_path" value="{$convert_path}"/>
							</td>
						</tr>
						<tr>
							<th>
								<label for="table_prefix">Table Prefix (optional)</label>
							</th>
							<td>
								<input type="text" name="table_prefix" value="{$conf.table_prefix}"/>
							</td>
						</tr>
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
								<label for="db_host">Database Host</label>
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
								<label for="db_user">Database User</label>
							</th>
							<td>
								<input type="text" name="db_user" value="{$conf.db.user}"/>
							</td>
						</tr>
						<tr>
							<th>
								<label for="db_pass">Database Password</label>
							</th>
							<td>
								<input type="text" name="db_pass" value="{$conf.db.pass}"/>
							</td>
						</tr>
						<tr id="db_path">
							<th>
								<label for="db_path">Database Path (SQLite only)</label>
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
						<tr>
							<th></th>
							<td>
								<input type="submit" id="save_settings_button" value="save settings"/>
								<input type="submit" id="init_db_button" class="hide" value="initialize database"/>
								<div id="init_db_msg"></div>
							</td>
						</tr>
						<tr>
							<th></th>
							<td>
								<input type="submit" id="setup_db_button" class="hide" value="create admin user and sample collection"/>
							</td>
						</tr>
						<tr>
							<th></th>
							<td>
								<textarea id="local_config_txt" class="hide" cols="80" rows="28" name="local_config"></textarea>
							</td>
						</tr>
					</table>
				</form>
			</div>
		</div>
	</body>
</html>
