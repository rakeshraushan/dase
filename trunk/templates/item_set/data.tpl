<html>
	<head>
		<base href="{$app_root}"/>
		<title>{$items->title}</title>
		<link rel="stylesheet" type="text/css" href="www/css/yui.css"/>
		<style type="text/css">
			{literal}
			div#container {
				margin: 20px auto;
				width: 95%;
			}
			table#data dt {
				font-weight: bold;
				color: #009;
			}
			table#data th {
				border: 1px solid #999;
				background-color: #eee;
			}
			table#data td {
				padding: 12px;
				border: 1px solid #999;
			}
			{/literal}
		</style>
	</head>
	<body>
		<div id="container">
			<h1>{$items->title}</h1>
			<table id="data">
				{foreach item=it from=$items->entries}
				<tr>
					<th>
						<img src="data:image/png;base64,{$it->viewitemBase64}"/>
						<h3>{$it->collection}</h3>
					</th>
					<td>
						<dl>
							{foreach item=set key=ascii_id from=$it->metadata}
							<dt>{$set.attribute_name}</dt>
							{foreach item=value from=$set.values}
							<dd>
							{$value.text}
							</dd>
							{/foreach}
							{/foreach}
						</dl>
					</td>
				</tr>
				{/foreach}
			</table>
		</div>
	</body>
</html>
