{extends file="admin/layout.tpl"}

{block name="head"}
<script type="text/javascript">
	{literal}
	Dase.pageInitUser = function(eid) {
		var link = Dase.$('expunge_cache_link');
		link.onclick = function() {
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
	<ol>
		<li>
		<h3><a href="admin/cache" class="delete" id="expunge_cache_link">expunge cache files</a></h3>
		</li>

	</ol>
{/block} 


