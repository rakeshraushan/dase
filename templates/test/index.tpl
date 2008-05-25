{extends file="test/layout.tpl"}

{block name="title"}DASe Tests{/block} 

{block name="tests"}
<h2>Available Tests</h2>
<ul>
{foreach item=t from=$tests}
<li><a href="{$app_root}test/{$t}">{$t}</a></li>
{/foreach}
</ul>
{/block}


{block name="test-data"}
<div id="test-data">
	{if 0 == $test->failed}
	<div class="masthead success"><h1>DASe Tests</h1></div>
	{else}
	<div class="masthead failed"><h1>DASe Tests</h1></div>
	{/if}
	<h5 class="test {$test->result}">{$test->name}</h5>
	<h5>{$test->failed} failed out of {$test->total} run</h5>
</div>
{/block}


