{extends file="tools/layout.tpl"}

{block name="head"}
<script type="text/javascript" src="www/js/dase/atompub.js"></script>
<script type="text/javascript" src="www/js/dase/htmlbuilder.js"></script>
<script type="text/javascript" src="www/js/dase/htmlbuilder_demo.js"></script>
{/block}

{block name="title"}DASe Tools{/block} 

{block name="content"}
<div id="demo">
	<h1>HTML Builder Demo</h1>
	<div id="htmlDisplay">loading...</div>
	<textarea cols="80" rows="80" id="atomDisplay">loading...</textarea>

</div>
{/block}

