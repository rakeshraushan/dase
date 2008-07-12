{extends file="manage/layout.tpl"}

{block name="js_include"}dase/manage/collection_form.js{/block}

{block name="title"}DASe: Add a Collection{/block} 

{block name="content"}
<div class="list" id="browse">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	<h1>Add a Collection:</h1>
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
<!-- javascript template -->
<textarea class="javascript_template" id="atom_jst">
	{literal}
	<entry xmlns="http://www.w3.org/2005/Atom">
	<title>${collection_name}</title>
	<id>${id}</id>
	<updated>${date}</updated>
	<category term="collection" scheme="http://daseproject.org/category/entrytype"/>
	<content type="text">${ascii_id}</content>
	<author><name>${eid}</name></author>
	</entry>
	{/literal}
</textarea>
<!-- end javascript template -->
{/block}
