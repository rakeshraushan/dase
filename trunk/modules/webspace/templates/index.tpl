{extends file="manage/layout.tpl"}

{block name="head"}
<link rel="stylesheet" type="text/css" href="{$module_root}css/style.css">
<script type="text/javascript" src="{$module_root}scripts/webspace.js"></script>
{/block}

{block name="content"}
<div class="container">
	<h1>UTexas WebSpace Ingester</h1>
	<div class="content">
		<form action="manage/{$collection->ascii_id}/webspace" method="get">
			<h3>enter webspace user account name (usually EID)</h3>
			<input type="text" name="webspace_name" value="{$webspace_name}"/>
			<input type="submit" value="retrieve file listing"/>
		</form>
		{if $files|@count}
		<div class="file_list">
			<form action="ingester">
				<ul class="multicheck" id="fileList">
					{foreach item=file from=$files}
					<li>
					<input type="checkbox" checked="checked" value="{$file.url}" name="file_to_upload"/>
					<a class="checked" href="{$file.url}">{$file.name}</a>
					<span class="filesize">({$file.length}K)</span>
					</li>
					{/foreach}
				</ul>
				<p class="checker">
				<a href="#" id="checkall">check/uncheck all</a>
				</p>
				<input type="submit" value="upload checked files"/>
			</form>
		</div>
		{/if}
	</div>
</div>
{/block}
