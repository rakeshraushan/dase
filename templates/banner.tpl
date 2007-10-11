{if $user}
<div class="login controls">
{$user->name} is logged in. (<a href="logoff" class="logoff">logoff</a>)
</div>
<div class="hide" id="userData">{$user->eid}</div>
{/if}

<div id="wordmark">
<a href="http://www.utexas.edu"><img src="images/UTwordmark_02.jpg" alt="ut logo"></a>
</div>

{if $cb || $temp_cb}
<div class="adminBanner">
DASE Collection Builder
<p>{$cb_name}</p>
</div>
{else}
<div class="daseBanner"></div>
{/if}
