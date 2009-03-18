<!doctype html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>Proposal: {$proposal->title.text}</title>
		<meta http-equiv="content-type" content="text/html;charset=utf-8">
		<base href="{$module_root}">
		<meta http-equiv="Content-Style-Type" content="text/css"> 

		<link rel="stylesheet" type="text/css" href="css/preview_eval.css">
		<link rel="stylesheet" type="text/css" href="css/print.css" media="print">
		<script type="text/javascript" src="{$app_root}www/scripts/json2.js"></script>
		<script type="text/javascript" src="{$app_root}www/scripts/dase.js"></script>
		<script type="text/javascript" src="{$app_root}www/scripts/md5.js"></script>
		<script type="text/javascript" src="scripts/preview.js"></script>
	</head>
	<body>
		<div id="submission" {if 'yes' == $submitted}class="hide"{/if}>
			<div class="inner">
				{if $proposal->proposal_submitted.text}
				<h3>Proposal was submitted {$proposal->proposal_submitted.text|date_format:"%a, %b %e %Y at %l:%M%p"}
					<!-- <span class="miniLink">(<a href="home">return</a>)</span>--></h3>
				{if $request->is_superuser}
				<div class="controls">
					<form>
						<input id="unsubmitFormButton" action="{$propLink}/unarchiver" type="submit" value="UnSubmit Proposal">
					</form>
				</div>
				{/if}
				{else}
				{if $person->person_eid.text == $user->eid || $request->is_superuser}
				<!--
				<p><a href="{$propLink}">return to proposal form to continue editing</a></p>
				<strong>OR</strong>
				-->
				<form id="submitForm" method="post" action="{$propLink}/archiver">
					<input id="submitFormButton" type="submit" value="Submit Proposal Now">
					<input name="chair_name" value="{$chair_name}" type="hidden">
					<input name="chair_email" value="{$chair_email}" type="hidden">
				</form>
				<p>[Please make sure your proposal is complete and looks as it should. If not, return to proposal form and make sure that you have clicked ‘UPDATE’ for each section. After you submit the proposal, it will become available to your department chair, {$chair_name} ({$chair_email}), for review, and you will no longer be able to make changes.]</p>
				{/if}
				{/if}
			</div>
		</div>
		<div id="container">
			<h1>Proposal: {$proposal->title.text}</h1>
			<dl>
				<dt><h3>Submitter:</h3></dt>
				<dd>{$person->person_name.text}</dd>
				<dt><h3>Department:</h3></dt>
				<dd>{$proposal->department}</dd>
				<dt><h3>Project Type:</h3></dt>
				<dd>{$proposal->proposal_project_type.text}</dd> 
			</dl>
			<h2>Collaborators</h2>
			{$proposal->proposal_collaborators.text}
			<h2>Summary</h2>
			{$proposal->proposal_summary.text|nl2br}
			<h2>Description</h2>
			{$proposal->proposal_description.text|nl2br}
			<h2>Students and Classes Served</h2>
			<p>
			<ul>
				{foreach item=course from=$courses}
				<li>
				{$course.metadata.course_title}
				({$course.metadata.course_number}) 
				[{$course.metadata.course_enrollment} students {$course.metadata.course_frequency}]
				</li>
				{/foreach}
			</ul>
			</p>
			<h2>Previous Funding</h2>
			{$proposal->proposal_previous_funding.text|nl2br}
			<h2>Student Technology Assistant Requested?</h2>
			<p>{$proposal->proposal_sta.text}</p>
			<h2>Summer Faculty Workshop Requested?</h2>
			<p>{$proposal->proposal_faculty_workshop.text}</p>
			<h2>Professional Assistance</h2>
			{$proposal->proposal_professional_assistance.text|nl2br}
			<h2>Renovation Description</h2>
			{$proposal->proposal_renovation_description.text|nl2br}
			<h2>Budget Description</h2>
			{$proposal->proposal_budget_description.text|nl2br}
			<h2>Itemized Budget</h2>
			<div class="tdiv">
				<table>
					<tr>
						<th>type</th>
						<th>description</th>
						<th>quantity</th>
						<th>price</th>
						<th>total</th>
					</tr>
					{foreach item=item from=$budget_items}
					<tr>
						<td>{$item.metadata.budget_item_type}</td>
						<td>{$item.metadata.budget_item_description}</td>
						<td>{$item.metadata.budget_item_quantity}</td>
						<td>${$item.metadata.budget_item_price}</td>
						<td>${$item.total}</td>
					</tr>
					{/foreach}
					<tr>
						<td colspan="4">grand total:</td>
						<td>${$grand_total}</td>
					</tr>
				</table>
			</div>
		</div>
	</body>
</html>
