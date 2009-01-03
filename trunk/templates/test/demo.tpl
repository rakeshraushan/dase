{extends file="test/layout.tpl"}

{block name="head"}
<script type="text/javascript" src="www/scripts/dase/demo.js"></script>
{/block}

{block name="title"}DASe Tests{/block} 

{block name="tests"}
<h2>Available Tests</h2>
<ul>
	{foreach item=t from=$tests}
	<li><a href="{$app_root}test/{$t}">{$t}</a></li>
	{/foreach}
</ul>
{/block}


{block name="content"}
<div id="demo">
	<h1>Atom/AtomPub Demo</h1>
	<div class="demoForm">
		<form id="demoForm">
			<p>
			<input type="text" value="{$url}" name="path"/>
			<p>
			<input id="submitGet" type="submit" value="GET"/>
			<input id="submitDelete" type="submit" value="DELETE"/>
			</p>
			</p>
			<p>
			<textarea name="formText" rows="25">{$atom_doc}</textarea>
			</p>
			<p>
			<input id="submitPut" type="submit" value="PUT"/>
			<input id="submitPost" type="submit" value="POST"/>
			</p>
		</form>
	</div>
	<div id="atomDisplay"></div>
</div>
{/block}

