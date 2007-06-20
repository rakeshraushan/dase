<div class="content list" id="browse">
<a id="content" name="content"></a>

{if $msg}
<div class="alert">{$msg}</div>
{/if}

<h1>Please Login with your OpenID</h1>

<form id="login" action="openid/login" method="get">
<p>
<label for="openid">Identity URL</label>
<input type="text" name="openid">
</p>
<p>
<input type="submit" value="verify">
</p>
</form>

{if $success}
{/if}


</div> 
