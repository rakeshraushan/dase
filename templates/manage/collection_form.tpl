{extends file="manage/layout.tpl"}

{block name="head"}
<script type="text/javascript" src="www/scripts/dase/manage/collection_form.js"></script>
{/block}

{block name="title"}DASe: Add a Collection{/block} 

{block name="content"}
<div class="list" id="browse">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	<h1>Add a Collection:</h1>
	//this form data will be transformed into atom
	//using js template (see below)
	<form id="newCollection" action="collections" method="post">
		<p>
		<label for="collection_name">Collection Name:</label>
		<input type="text" id="collection_name" name="collection_name"/>
		</p>
		<p>
		<label for="media_repository">Media Repository (leave blank for default):</label>
		<input type="text" id="media_repository" name="media_repository"/>
		</p>
		<p>
		<input type="submit" value="create"/>
		</p>
	</form>
</div>
<!-- javascript template -->
<textarea class="javascript_template" id="atom_jst">
	{literal}
	<entry xmlns="http://www.w3.org/2005/Atom">
	<title>${collection_name}</title>
	<id>${id}</id>
	<updated>${date}</updated>
	<link rel="http://daseproject.org/relation/media-collection" href="${media_repository}"/>
	<category term="collection" scheme="http://daseproject.org/category/entrytype"/>
	<content type="text">${ascii_id}</content>
	<author><name>${eid}</name></author>
	</entry>
	{/literal}
</textarea>
<!-- end javascript template -->
{/block}
