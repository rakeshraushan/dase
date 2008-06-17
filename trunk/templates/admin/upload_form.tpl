{extends file="admin/layout.tpl"}
{block name="title"}DASe: Upload Item{/block} 

{block name="content"}
<div class="list" id="browse">
	{if $msg}<h3 class="alert">{$msg}</h3>{/if}
	<h1>Check your Atom Document:</h1>
	<form action="cb/{$user->eid}/{$collection->ascii_id}/check_atom" method="post" enctype="multipart/form-data">
		<p>
		<input type="file" name="atom"/>
		<input type="submit" value="check sytntax"/>
		</p>
	</form>
</div>
{/block}
