<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<base href="{$module_root}"/>
		<title>HRMS Trainee Information</title>
		<link rel="stylesheet" type="text/css" href="{$app_root}www/css/yui.css"/>
		<link rel="stylesheet" type="text/css" href="{$app_root}www/css/style.css">
		<link rel="stylesheet" type="text/css" href="{$module_root}css/style.css">
		<script type="text/javascript" src="{$app_root}www/scripts/http.js"></script> 
		<script type="text/javascript" src="{$app_root}www/scripts/json2.js"></script> 
		<script type="text/javascript" src="{$app_root}www/scripts/dase.js"></script> 
		<script type="text/javascript" src="{$app_root}www/scripts/dase/form.js"></script> 
		<script type="text/javascript" src="scripts/forms.js"></script> 
	</head>
	<body>
		<div class="container">
			<a href="{$app_root}logoff" class="edit" id="logoff-link">logout {$data}</a>
			<div class="branding">
				Human Resource Management System (HRMS) Trainees 
			</div>
			<div class="content">
				<dl>
					<dt>Your Name:</dt>
					<dd>{$user.name}</dt>
					<dt>Your EID:</dt>
					<dd>{$user.eid}</dt>
					<dt>Your Unit:</dt>
					<dd>{$user.unit}</dt>
				</dl>
				<h1>Trainees</h1>
				<table class="trainee">
					<tr>
						<th>
							<label for="submitter__name">Submitter Name</label>
						</th>
						<th>
							<label for="submitter_eid">Submitter EID</label>
						</th>
						<th>
							<label for="submitter_dept">Submitter Dept</label>
						</th>
						<th>
							<label for="first_name">First Name</label>
						</th>
						<th>
							<label for="last_name">Last Name</label>
						</th>
						<th>
							<label for="email">Email</label>
						</th>
						<th>
							<label for="eid">EID</label>
						</th>
						<th>
							<label for="logon_id">Logon ID</label>
						</th>
						<th>
							<label for="Electronic Office">Electronic Office</label>
						</th>
						<th>
							<label for="Electronic Desk">Electronic Desk</label>
						</th>
					</tr>
					{foreach item=it from=$feed->entries}
					<tr>
						<td>
							{$it|select:'submitter_name'}
						</td>
						<td>
							{$it|select:'submitter_eid'}
						</td>
						<td>
							{$it|select:'submitter_dept'}
						</td>
						<td>
							{$it|select:'first_name'}
						</td>
						<td>
							{$it|select:'last_name'}
						</td>
						<td>
							{$it|select:'email'}
						</td>
						<td>
							{$it|select:'eid'}
						</td>
						<td>
							{$it|select:'logon_id'}
						</td>
						<td>
							{$it|select:'eoffice'}
						</td>
						<td>
							{$it|select:'edesk'}
						</td>
					</tr>
					{/foreach}
				</table>
				<h3><a href="index">return to form</a></h3>
			</div>
		</div>
		<div class="footer">
			<img src="{$app_root}www/images/its.gif" title="LAITS" alt="LAITS" align="middle" height="33" width="79"><a href="http://www.laits.utexas.edu/its/" target="_blank">Liberal Arts ITS</a>
			| <a href="mailto:dase@mail.laits.utexas.edu">email</a> 
			|
			 
			<a href="http://daseproject.org"><img 11="" alt="DASe powered icon" title="DASe powered!" src="{$app_root}www/images/dasepowered.png" height="" width="71"></a>
		</div>
	</body>
</html>
