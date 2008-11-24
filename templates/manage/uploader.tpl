{extends file="manage/layout.tpl"}

{block name="head"}
<script type="text/javascript" src="www/scripts/dase/uploader.js"></script>
{/block}

{block name="title"}DASe: Create New Item{/block} 

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

	<ul id="recent">
	</ul>

</div>
<!-- javascript template for recent-->
<textarea class="javascript_template" id="recent_jst">
	{literal}
	{for sernum in recent}
	<li>
	<a href='${sernum.item_record_href}'><img src="${sernum.thumbnail_href}"/></a>
	<h4>${sernum.title}</h4>
	</li>
	{/for}
	{/literal}
</textarea>
<!-- end javascript template -->
{/block}
