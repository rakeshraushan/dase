{extends file="base.tpl"}

{block name="topline"}
<a href="login/{$request->user->eid}" class="delete">logout {$request->user->eid}</a> 
{/block}

{block name="title"}AudioCat{/block}

{block name="header"}
<div class="header-inner">
	<h1><a href="home">LAITS AudioCat</a></h1>
</div>
<div class="header-form">
	<form action="home/search" method="get">
		<input type="text" name="q">
		<select name="field">
			<option value="">all fields</option>
			<option value="title">Title</option>
			<option value="author">Author</option>
			<option value="language">Language</option>
			<option value="number">Number</option>
			<option value="level">Level</option>
			<option value="class">Class</option>
			<option value="description">Description</option>
		</select>
		<input type="submit" value="search">
	</form>
</div>
<div class="clear"></div>
{/block}


{block name="main"}
{if $msg}<h3 class="msg">{$msg}</h3>{/if}
{block name="new_item_link"}
{if $request->user->can_edit}
<div class="controls">
	<a href="item/new">create a new item</a>
</div>
<div class="clear"></div>
{/if}
{/block}
{block name="content"}default content{/block}
{/block}

{block name="footer"}
<div class="brand">
	<table class="logo">
		<tr><td class="a1">&nbsp;</td><td class="a2">&nbsp;</td><td class="a3">&nbsp;</td><td class="a4">&nbsp;</td><td class="a5">&nbsp;</td></tr>
		<tr><td class="b1">&nbsp;</td><td class="b2">&nbsp;</td><td class="b3">&nbsp;</td><td class="b4">&nbsp;</td><td class="b5">&nbsp;</td></tr>
		<tr><td class="c1">&nbsp;</td><td class="c2">&nbsp;</td><td class="c3">&nbsp;</td><td class="c4">&nbsp;</td><td class="c5">&nbsp;</td></tr>
		<tr><td class="d1">&nbsp;</td><td class="d2">&nbsp;</td><td class="d3">&nbsp;</td><td class="d4">&nbsp;</td><td class="d5">&nbsp;</td></tr>
		<tr><td class="e1">&nbsp;</td><td class="e2">&nbsp;</td><td class="e3">&nbsp;</td><td class="e4">&nbsp;</td><td class="e5">&nbsp;</td></tr>
	</table>
	<div class="label">
		<a href="http://www.laits.utexas.edu/its/"><strong>Liberal Arts</strong> Instructional Technology Services</a>
	</div>
</div>
{/block}
