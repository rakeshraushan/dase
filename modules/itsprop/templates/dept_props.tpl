{extends file="layout.tpl"}

{block name="content"}
<div class="main">
	<h1>Proposals for {$dept->dept_name.text}</h1>
	<h4>Chairperson: {$dept->dept_chair.text}</h4>
	<p><a href="ss" class="toggle" id="toggle_vision">add/edit vision statement</a></p>
	<form method="post" action="{$dept->editContentLink}" class="hide" id="vision">
		<textarea id="vision_text" name="vision">{$dept->content}</textarea>
		<p>
		<input type="submit" value="update">
		</p>
	</form>
	{foreach key=i item=proposal from=$props->entries}
	<div class="prop_rating">
		<h2>{$i+1}. {$proposal->proposal_name.text}</h2>
		<a href="proposal/{$proposal->serialNumber}/preview">preview</a>
		<h3>{$proposal->getParentLinkTitleByItemType('person')}</h3>
		<p><a href="ss" class="toggle" id="toggle_prop{$i+1}">add/edit comment</a></p>
		<form class="hide" id="prop{$i+1}">
			<textarea class="comment" name="comment"></textarea>
			<p>
			<input type="submit" value="update">
			</p>
		</form>
	</div>
	{/foreach}
</div>
{/block}

