<!doctype html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>Proposal: {$proposal->title}</title>
		<meta http-equiv="content-type" content="text/html;charset=utf-8">
		<base href="{$module_root}">
		<meta http-equiv="Content-Style-Type" content="text/css"> 

		<link rel="stylesheet" type="text/css" href="css/preview.css">
		<script type="text/javascript" src="scripts/preview.js"></script>
	</head>
	<body>
		<div id="submission" {if 'yes' == $submitted}class="hide"{/if}>
			<div class="inner">
				{if $proposal->proposal_submitted.text}
				<h3>Proposal was submitted {$proposal->proposal_submitted.text|date_format:"%a, %b %e %Y at %l:%M%p"} <span class="miniLink">(<a href="home">return</a>)</span></h3>
				{else}
				<p><a href="{$propLink}">return to proposal form to continue editing</a></p>
				<strong>OR</strong>
				<form id="submitForm" method="post" action="{$propLink}/archiver">
					<input type="submit" value="Submit Proposal Now">
				</form>
				<p>[Make sure your proposal is complete.  After you submit the proposal, it will become available to your department chair for review, and you will no longer be able to make changes.]</p>
				{/if}
			</div>
		</div>
		<div id="container">
			<h1>Proposal: {$proposal->title}</h1>
			<dl>
				<dt>Submitter:</dt>
				<dd>{$person->person_name.text}</dd>
				<dt>Department:</dt>
				{foreach item=plink from=$person->parentLinks}
				{if 'department' == $plink.item_type}
				{assign var=dept_title value=$plink.title}
				{/if}
				{/foreach}	
				<dd>{$dept_title}</dd>
				<dt>Project Type:</dt>
				<dd>{$proposal->proposal_project_type.text}</dd> 
			</dl>
			<h2>Collaborators</h2>
			{$proposal->proposal_collaborators.text}
			<h2>Summary</h2>
			{$proposal->proposal_summary.text|markdown}
			<h2>Description</h2>
			{$proposal->proposal_description.text|markdown}
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
			{$proposal->proposal_previous_funding.text|markdown}
			<h2>Student Technology Assistant Requested?</h2>
			<p>{$proposal->proposal_sta.text}</p>
			<h2>Summer Faculty Workshop Requested?</h2>
			<p>{$proposal->proposal_faculty_workshop.text}</p>
			<h2>Professional Assistance</h2>
			{$proposal->proposal_professional_assistance.text|markdown}
			<h2>Renovation Description</h2>
			{$proposal->proposal_renovation_description.text|markdown}
			<h2>Itemized Budget</h2>
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
			<h2>Budget Description</h2>
			{$proposal->proposal_budget_description.text|markdown}
		</div>
	</body>
</html>
