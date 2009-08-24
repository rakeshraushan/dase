{extends file="layout.tpl"}

{block name="head"}
<script type="text/javascript" src="js/jquery.js"></script> 
<script type="text/javascript" src="js/autosuggest.js"></script> 
<script type="text/javascript" src="js/onda_jquery.js"></script> 
{/block}


{block name="content"}

<form action="search_all" method="get" id="search">

	<div class="grid_3 row">

		<span class="grid_3">
			<label>Search:</label>
		</span>
		<br/>
		<input type="text" name="term" class="search-term" />


		<br />
		<label class="grid_3">Keyword: (optional)</label>
		<br/>
		<input id="kwterm" type="text" name="kwterm" class="search-term" />
		<div id="autosuggest"><ul></ul></div>
		<div class="clear">&nbsp;</div>	


	</div>
	<div class="grid_1 float-r submit">
		<input type="submit" value="Search" class="submit-btn" />
	</div>

	<div class="clear">&nbsp;</div>	

</form>
<dl>
	{assign var=sorted_feed value=$feed|sortby:'title'}
	{foreach item=entry from=$sorted_feed->entries}
	<dt>{$entry->title.text}</dt>
	<dd>{$entry->description.text}</dd>
	{/foreach}
</dl>

{/block}



