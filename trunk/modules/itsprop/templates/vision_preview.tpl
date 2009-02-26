<!doctype html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>Proposal: {$proposal->title}</title>
		<meta http-equiv="content-type" content="text/html;charset=utf-8">
		<base href="{$module_root}">
		<meta http-equiv="Content-Style-Type" content="text/css"> 

		<link rel="stylesheet" type="text/css" href="css/preview.css">
		<link rel="stylesheet" type="text/css" href="css/print.css" media="print">
		<script type="text/javascript" src="{$app_root}www/scripts/json2.js"></script>
		<script type="text/javascript" src="{$app_root}www/scripts/dase.js"></script>
		<script type="text/javascript" src="{$app_root}www/scripts/md5.js"></script>
		<script type="text/javascript" src="scripts/preview.js"></script>
	</head>
	<body>
		<div id="container">
			<h1>{$dept->dept_name.text} Vision Statement</h1>
			<h4>Chairperson: {$dept->dept_chair.text}</h4>
			<div id="vision_statement">{$dept->content|nl2br}</div>
			<h4>Ranked Proposals</h4>
			{if $props->entries}
			<ul id="proposals_list_preview">
				{assign var=sorted_feed value=$props|sortby:'proposal_chair_rank'}
				{foreach key=i item=proposal from=$sorted_feed->entries}
				<li><span>{$proposal->proposal_chair_rank.text}. <strong>{$proposal->proposal_name.text}</strong></span></li> 
				{/foreach}
			</ul>
			{/if}
		</div>
	</body>
</html>
