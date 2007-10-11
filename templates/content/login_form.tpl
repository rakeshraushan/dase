<div class="content list" id="browse">

{if $msg}
<div class="alert">{$msg}</div>
{/if}

<h1>Please Login to Dase:</h1>

<form id="login" action="login" method="post">
<p>
<label for="username-input">username:</label>
<input type="text" id="username-input" name="username">
</p>
<p>
<label for="password-input">password:</label>
<input type="password" id="password-input" name="password">
</p>
<p>
<input type="submit" value="login">
</p>
</form>
</div>
