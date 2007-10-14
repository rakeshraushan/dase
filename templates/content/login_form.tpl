<div class="content list" id="browse">

<div id="msg" class="alert{if !$msg} hide{/if}">{$msg}</div>

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
