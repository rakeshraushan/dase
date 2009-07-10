{extends file="admin/layout.tpl"}

{block name="head"}
<script type="text/javascript" src="www/scripts/dase/atompub.js"></script>
<script type="text/javascript" src="www/scripts/dase/htmlbuilder.js"></script>
<script type="text/javascript" src="www/scripts/dase/collection_form.js"></script>
{/block}

{block name="title"}DASe: Add a Collection{/block} 

{block name="content"}
<div class="list" id="browse">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	<h1>Add a Collection:</h1>
	<!--this form data will be transformed into atom
	using js template (see below)
	-->
	<form id="newCollection" action="collections" method="post">
		<p>
		<label for="collection_name">Collection Name:</label>
		<input type="text" id="collection_name" name="collection_name"/>
		</p>
		<p>
		<input type="submit" value="create"/>
		</p>
	</form>
</div>
{/block}
