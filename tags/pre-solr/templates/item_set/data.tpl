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
			table#data th {
				border: 1px solid #999;
				background-color: #eee;
			}
			table#data td {
				padding: 12px;
				border: 1px solid #999;
			}
			table#metadata {
			}

			table#metadata th {
				text-align: right;
				vertical-align: top;
				color: #339;
				border: 0px solid #ddf;
				padding: 2px;
				width: 160px;
				background-color: #fff;
			}

			table#metadata td {
				text-align: left;
				padding: 2px;
				border: 0px solid #ddf;
				width: 500px;
			}

			table#metadata li a {
				font-weight: normal;
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
						<table id="metadata" class="{$it->collectionAsciiId}">
							{foreach item=set key=ascii_id from=$it->metadata}
							<tr>
								<th>{$set.attribute_name}</th>
								<td>
									<ul>
										{foreach item=value from=$set.values}
										<li>
										<a href="search?{$it->collectionAsciiId}.{$ascii_id}={$value.text|escape:'url'}">{$value.text}</a>
										</li>
										{/foreach}
									</ul>
								</td>
								{/foreach}
							</tr>
						</table>
						<!--
						<dl id="metadata" class="{$it->collectionAsciiId}">
							{foreach item=set key=ascii_id from=$it->metadata}
							<dt>{$set.attribute_name}</dt>
							{foreach item=value from=$set.values}
							<dd><a href="search?{$it->collectionAsciiId}.{$ascii_id}={$value.text|escape:'url'}">{$value.text}</a></dd>
							{/foreach}
							{/foreach}
						</dl>
						-->
					</td>
				</tr>
				{/foreach}
			</table>
		</div>
	</body>
</html>
