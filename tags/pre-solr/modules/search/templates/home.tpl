{extends file="layout.tpl"}

{block name="content"}

<h1>Search DAse</h1>
<h3>{$q}</h3>
<form action="search" method="get">
	<label for="q">Search Term(s):</label>
	<input type="text" name="q">
	<input type="submit" value="go">
</form>


{$results}


{/block}



