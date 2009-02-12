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


{block name="content"}
<div id="test-data">
	{if 0 == $test->failed}
	<h1>DASe Test Results <img src="www/images/tango-icons/weather-clear.png"/></h1>
	{else}
	<h1>DASe Test Results <img src="www/images/tango-icons/weather-showers.png"/></h1>
	{/if}
	<h5 class="test {$test->result}">{$test->name}</h5>
	<h5>{$test->failed} failed out of {$test->total} run</h5>
</div>
{/block}


