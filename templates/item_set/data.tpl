<html>
	<head>
		<base href="{$app_root}"/>
		<title>{$items->title}</title>
		<link rel="stylesheet" type="text/css" href="www/css/yui.css"/>
		<style type="text/css">
			{literal}
			table#data {
				font-size: .9em;
			}
			table#data th {
				border: 1px solid #666;
				background-color: #eee;
			}
			table#data td {
				border: 1px solid #999;
			}
			{/literal}
		</style>
	</head>
	<body>
		{if $is_single_collection}
		<table id="data">
			{foreach item=it from=$items->entries}
			{if !$seen}
			<tr>
				<th>Thumbnail</th>
				{foreach item=set key=ascii_id from=$it->metadata}
				<th>{$set.attribute_name}</th>
				{/foreach}
			</tr>
			{/if}
			{assign var="seen" value="1"}
			<tr>
				<td>
					<img src="data:image/png;base64,{$it->thumbnailBase64}"/>
				</td>
				{foreach item=set key=ascii_id from=$it->metadata}
				<td>
					{foreach item=value from=$set.values}
					{$value}
					{/foreach}
				</td>
				{/foreach}
			</tr>
			{/foreach}
		</table>
		{else}
		<h1 class="alert">Sorry, only single-collection sets (all items are from the same DASe collection) can be viewed in "data" mode</h1>
		{/if}
	</body>
</html>
