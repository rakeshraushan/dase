<!doctype html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>Proposal: {$proposal->title.text}</title>
		<meta http-equiv="content-type" content="text/html;charset=utf-8">
		<base href="{$module_root}">
		<meta http-equiv="Content-Style-Type" content="text/css"> 

		<link rel="comments" type="application/json" href="{$app_root}item/itsprop/{$proposal->serialNumber}/comments.json">
		<link rel="stylesheet" type="text/css" href="css/preview_eval.css">
		<link rel="stylesheet" type="text/css" href="css/print.css" media="print">
		<script type="text/javascript" src="{$app_root}www/scripts/json2.js"></script>
		<script type="text/javascript" src="{$app_root}www/scripts/dase.js"></script>
		<script type="text/javascript" src="{$app_root}www/scripts/dase/htmlbuilder.js"></script>
		<script type="text/javascript" src="{$app_root}www/scripts/md5.js"></script>
		<script type="text/javascript" src="scripts/jquery-1.2.6.js"></script>
		<script type="text/javascript" src="scripts/preview.js"></script>
	</head>
	<body>
		<div id="topMenu">
			<a href="#" id="prop-link" class="here">proposal</a>  
			<a href="#" id="vision-link">department vision statement</a>  
			<a href="#" id="notes-link">evaluator notes</a>  
			<a href="proposals" class="skip">list all proposals</a>  
		</div>
		<div id="container">
			<div id="prop">
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
			<div id="vision">
				<h1>{$dept->dept_name.text} Vision Statement</h1>
				<h4>Chairperson: {$dept->dept_chair.text}</h4>
				<div id="vision_statement">{$dept->content|nl2br}</div>
				<h4>Ranked Proposals</h4>
				{if $props->entries}
				<ul id="proposals_list_preview">
					{assign var=sorted_feed value=$props|sortby:'proposal_chair_rank'}
					{foreach key=i item=propo from=$sorted_feed->entries}
					<li><span>{$propo->proposal_chair_rank.text}. <strong>{$propo->proposal_name.text}</strong></span></li> 
					{/foreach}
				</ul>
				{/if}
			</div>
			<div id="notes">
				<h1>{$proposal->title.text} -- comments</h1>
				<p>
				<form id="addCommentForm" action="{$app_root}item/itsprop/{$proposal->serialNumber}/comments.json" method="post">
					<h3>add a comment</h3>
					<textarea name="comment" cols="60" rows="6"></textarea>
					<input type="hidden" name="commenter" value="{$user->name}">
					<input type="submit" value="submit">
				</form>
				<dl id="comments"></dl>
			</div>
		</div>
	</body>
</html>
