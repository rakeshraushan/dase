{extends file="layout.tpl"}
{block name="title"}DASe standard media attributes{/block} 

{block name="content"}
<div class="list" id="browse">
	<h1>Media File Attributes</h1>
	{foreach item=ma from=$attributes}
	<form method="post" action="media/attribute/{$ma->id}" class="adminForm">
		<div>
			<label for="term">term</label>
			<input type="text" name="term" value="{$ma->term}"/>
		</div>
		<div>
			<label for="label">label</label>
			<input type="text" name="label" value="{$ma->label}"/>
			<input type="submit" value="update" name="action"/>
			<!--
			<input type="submit" value="delete" name="action"/>
			-->
		</div>
	</form>
	{/foreach}
</div>
{/block}

