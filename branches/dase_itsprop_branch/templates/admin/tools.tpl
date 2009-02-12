{extends file="admin/layout.tpl"}

{block name="head"}
<script type="text/javascript">
	{literal}
	Dase.pageInitUser = function(eid) {
		Dase.$('expunge_cache_link').onclick = function() {
			Dase.ajax(this.href,'delete',function(resp) {
					alert(resp);
					},null,Dase.user.eid,Dase.user.htpasswd);
			return false;
		}
		Dase.$('truncate_log').onclick = function() {
			Dase.ajax(this.href,'delete',function(resp) {
					alert(resp);
					},null,Dase.user.eid,Dase.user.htpasswd);
			return false;
		}
	}
	{/literal}
</script>
{/block}

{block name="content"}
<div id="contentHeader">
	<h1>Maintenance Tools</h1>
	<ul>
		<li>
		<h3><a href="admin/cache" class="delete" id="expunge_cache_link">expunge cache files</a></h3>
		</li>
		<li>
		<h3><a href="admin/log" class="delete" id="truncate_log">truncate log</a></h3>
		</li>

	</ul>
{/block} 


