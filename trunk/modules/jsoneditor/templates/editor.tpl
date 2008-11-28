<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html>
	<head>
		<base href="{$module_root}/"/>
		<title>JSON Editor</title>
		<link rel="stylesheet" type="text/css" href="{$module_root}/css/style.css">
		<script src="{$app_root}www/scripts/json2.js"></script>
		<script src="{$app_root}www/scripts/dase.js"></script>
		<script src="scripts/JSONeditor.js"></script>
		<script src="scripts/dase_json.js"></script>
	</head>
	<body>
		<h1>JSON Editor</h1>
		<div id="tree"></div>
		<div id="jform">
			<form name="jsoninput" onsubmit="return treeBuilder.jsonChange(this)">
				<div id="jExamples">Load an example:
					<select name="jloadExamples" onchange="JSONeditor.loadExample(this.value)">
						<option value="0">None/empty</option>
						<option value="1">Employee data</option>
						<option value="2">Sample Konfabulator Widget</option>
						<option value="3">Member data</option>
						<option value="4">A menu system</option>
						<option value="5">The source code of this JSON editor</option></select>
					<br>
					<br></div>Label:
				<br>
				<input name="jlabel" type="text" value="" size="60" style="width:400px">
				<br>
				<br>Value: 
				<br>
				<textarea id="jvalue" name="jvalue" rows="10" cols="50" style="width:400px"></textarea>
				<br>
				<br>Data type: 
				<select onchange="treeBuilder.changeJsonDataType(this.value,this.parentNode)" name="jtype">
					<option value="object">object</option>
					<option value="array">array</option>
					<option value="function">function</option>
					<option value="string">string</option>
					<option value="number">number</option>
					<option value="boolean">boolean</option>
					<option value="null">null</option>
					<option value="undefined">undefined</option></select>
				<input name="orgjlabel" type="hidden" value="" size="50" style="width:300px">
				<input onfocus="this.blur()" type="submit" value="Save">
				<br>
				<br>
				<input name="jAddChild" onfocus="this.blur()" type="button" onclick="treeBuilder.jsonAddChild(this.parentNode)" value="Add child">
				<input name="jAddSibling" onfocus="this.blur()" type="button" onclick="treeBuilder.jsonAddSibling(this.parentNode)" value="Add sibling">
				<br>
				<br>
				<input name="jRemove" onfocus="this.blur()" type="button" onclick="treeBuilder.jsonRemove(this.parentNode)" value="Delete">
				<input name="jRename" onfocus="this.blur()" type="button" onclick="treeBuilder.jsonRename(this.parentNode)" value="Rename">
				<input name="jCut" onfocus="this.blur()" type="button" onclick="treeBuilder.jsonCut(this.parentNode)" value="Cut">
				<input name="jCopy" onfocus="this.blur()" type="button" onclick="treeBuilder.jsonCopy(this.parentNode)" value="Copy">
				<input name="jPaste" onfocus="this.blur()" type="button" onclick="treeBuilder.jsonPaste(this.parentNode)" value="Paste">
				<br>
				<br>
				<input type="checkbox" name="jbefore">Add children first/siblings before
				<br>
				<input type="checkbox" name="jPasteAsChild">Paste as child on objects & arrays
				<br>
				<br>
				<div id="jformMessage"></div></form>
		</div>
		<div id="items">
			<h3>load a JSON document</h3>
			<form id="daseJsonForm" method="post"
				action="{$app_root}collection/json_lists">
					<select name="docs">
						<option>select a document to load</option>
						{foreach item=item from=$collection->entries}{if 'application/json' == $item->contentType}
						<option value="{$item->contentSrc}">{$item->title}</option>
						{/if}{/foreach}
					</select>
					<p>
					<input type="submit" value="save current document as"/>
					<input type="text" name="title"/>
					</p>
			</form>
		</div>
	</body>
</html>
