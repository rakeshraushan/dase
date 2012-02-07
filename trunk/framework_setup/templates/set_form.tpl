{extends file="layout.tpl"}

{block name="content"}
<div class="controls">
	<a href="set/list">view sets</a>
</div>
<h1>Create a New Set</h1>
<form method="post">
	<label for="title">Title</label>
	<input type="text" name="title">
	<input type="submit" value="create set">
</form>
{/block}
