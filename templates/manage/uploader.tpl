{extends file="manage/layout.tpl"}

{block name="head"}
<script type="text/javascript" src="www/scripts/dase/uploader.js"></script>
{/block}

{block name="title"}DASe: Upload Item{/block} 

{block name="content"}
<div class="full">

	{if $msg}<h3 class="alert">{$msg}</h3>{/if}

	<h1>Create New Item</h1>

	<div class="uploader">
		<h4>Attach a File (optional)</h4>
		<form action="manage/{$collection->ascii_id}" method="post" enctype="multipart/form-data">
			<input type="file" name="uploaded_file" size="50"/>
			<p>
			<input type="submit" value="Create New Item"/>
			</p>
		</form>
	</div>

	<div class="recent">
		<h2>{$recent_uploads->title}</h2>
		<ul>
			{foreach item=item from = $recent_uploads->entries}
			<li><img src="{$item->thumbnailLink}"/><br/><a href="{$item->link}">{$item->title}</a></li>
			{/foreach}
		</ul>
	</div>
</div>
{/block}
