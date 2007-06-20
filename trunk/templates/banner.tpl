{if $user}
<div class="login controls">
{$user->name} is logged in. (<a href="user/logoff" class="logoff">logoff</a>)
</div>
{/if}
<a href="http://www.utexas.edu"><img src="images/UTwordmark_02.jpg" alt="ut logo"></a>
{if $cb || $temp_cb}
<div class="cbBanner">
DASE Collection Builder
<p>{$cb_name}</p>
</div>
{else}
<div class="cbBanner">
<p>DASE</p>
<p class="label"><strong>D</strong>igital <strong>A</strong>rchive <strong>SE</strong>rvices</p> 
</div>
{/if}
