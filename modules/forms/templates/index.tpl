<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<base href="{$module_root}"/>
		<title>HRMS Trainee Information</title>
		<link rel="stylesheet" type="text/css" href="{$app_root}www/css/yui.css"/>
		<link rel="stylesheet" type="text/css" href="{$app_root}www/css/style.css">
		<link rel="stylesheet" type="text/css" href="css/style.css">
		<script type="text/javascript" src="{$app_root}www/scripts/http.js"></script>
		<script type="text/javascript" src="{$app_root}www/scripts/json2.js"></script>
		<script type="text/javascript" src="{$app_root}www/scripts/dase.js"></script> 
		<script type="text/javascript" src="{$app_root}www/scripts/dase/form.js"></script> 
		<script type="text/javascript" src="scripts/forms.js"></script> 
	</head>
	<body>
		<div id="eid" class="pagedata">{$user.eid}</div>
		<div class="container">
			<a href="{$app_root}logoff" class="edit" id="logoff-link">logout {$user.eid}</a>
			<div class="branding">
				Human Resource Management System (HRMS) Trainees 
			</div>
			<div class="content">
				<dl>
					<dt>Your Name:</dt>
					<dd>{$user.name}</dd>
					<dt>Your EID:</dt>
					<dd>{$user.eid}</dd>
					<dt>Your Unit:</dt>
					<dd>{$user.unit}</dd>
				</dl>
				<p>Phase One of the new Human Resource Management System (HRMS) will launch on November 3, 2008. HRMS will replace the current Recruitment and Position Manager (RPM) and serve as the future means by which you will create a new position, reclassify a position, or fill an existing position.</p>

				<p>Phase One will include salaried classified and administrative & professional (A&P) titles that are paid monthly, as well as titles for librarians, UT Elementary School teachers, and teachers’ aides.</p>  

				<p>Student and faculty positions will NOT be included in Phase One, nor will a few other A&P exceptions, including: research professors, assistant and associate research professors, department chairs, Harrington fellows and UTemp positions.</p> 

				<p>Please list below those individuals (including yourself) who should receive HRMS training for Phase One. You may want to include both staff members who create recruiting documents and those who approve them.</p>
				<h1>Trainees</h1>
				<form action="data" method="post">
					<input type="hidden" name="submitter_name" value="{$user.name}"/>
					<input type="hidden" name="submitter_eid" value="{$user.eid}"/>
					<input type="hidden" name="submitter_dept" value="{$user.unit}"/>
					<table class="trainee">
						<tr>
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
							<th>
							</th>
						</tr>
						<tr>
							<td>
								<input type="text" name="first_name" value=""/>
							</td>
							<td>
								<input type="text" name="last_name" value=""/>
							</td>
							<td>
								<input type="text" name="email" value=""/>
							</td>
							<td>
								<input type="text" name="eid" value=""/>
							</td>
							<td>
								<input type="text" name="logon_id" value=""/>
							</td>
							<td>
								<input type="text" name="eoffice" value=""/>
							</td>
							<td>
								<input type="text" name="edesk" value=""/>
							</td>
							<td>
								<input type="submit" value="add"/>
							</td>
						</tr>
						{foreach item=it from=$feed->entries}
						<tr>
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
							<td>
								<a href="{$module_root}data/{$it->serialNumber}" class="delete">delete</a>
							</td>
						</tr>
						{/foreach}
					</table>
				</form>
				{if $admin_user}
				<h3><a href="data">view all</a> | <a href="csv">view all as csv </a></h3>
				{/if}
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
