{extends file="manage/layout.tpl"}

{block name="head"}
<script type="text/javascript" src="www/scripts/dase/manage/ingest_form.js"></script>
{/block}

{block name="title"}DASe: Ingest a Remote Collection{/block} 

{block name="content"}
<div class="list" id="browse">
	<h3 id="msg" class="alert">{$msg}</h3>
	<h1>Ingest a Remote Collection:</h1>
	<div id="indicator" class="alert hide"></div>
	<form id="ingestCollectionForm"  action="manage/ingest/checker" method="post">
		<p>
		<label for="collection_name">Remote Collection URL:</label>
		<input size="50" type="text" id="url" name="url"/>
		</p>
		<p>
		<input type="submit" value="Check URL"/>
		</p>
	</form>
</div>
{/block}
