<div class="content list" id="browse">

{if $msg}
<div class="alert">{$msg}</div>
{/if}

<h1>Please Login to Dase:</h1>

<form id="login" action="login" method="post">
<p>
<label for="username">username:</label>
<input type="text" name="username">
</p>
<p>
<label for="password">password:</label>
<input type="password" name="password">
</p>
<p>
<input type="submit" value="login">
</p>
</form>
</div>
