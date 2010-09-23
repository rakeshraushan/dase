{extends file="base.tpl"}

{block name="title"}title here{/block}

{block name="wordmark"}
<div id="universityWordMark">
	<a href="http://www.utexas.edu/cola"><img alt="UT College of Liberal Arts Wordmark" src="www/images/UTCOLA.jpg"/></a>
</div>
{/block}

{block name="header"}
<div class="header-inner">
	<h1><a href="home">Page Title</a></h1>
</div>
<div class="clear"></div>
{/block}

{block name="sidebar"}
<ul class="menu">
	<li>one</li>
	<li>two</li>
	<li>three</li>
</ul>
{/block}

{block name="main"}
{if $msg}<h3 class="msg">{$msg}</h3>{/if}
{block name="content"}default content{/block}
{/block}

{block name="footer"}
<div class="brand">
	<div class="label">
		<a href="http://www.laits.utexas.edu/its/"><strong>Liberal Arts</strong> Instructional Technology Services</a>
	</div>
</div>
{/block}
