{extends file="manage/layout.tpl"}

{block name="head"}
<script type="text/javascript">
	{literal}
	Dase.pageInit = function() {
		var url = Dase.base_href+'collections.json';
		Dase.getJSON(url,function(json){
				var data = { 'collections': json };
				var templateObj = TrimPath.parseDOMTemplate("collections_jst");
				Dase.$('cList').innerHTML = templateObj.process(data);
				var links = Dase.$('cList').getElementsByTagName('a');
				for (var i=0;i<links.length;i++) {
				if ('delete' == links[i].className) {
				links[i].onclick = function() {
				if (!confirm('Do you REALLY want to delete \n'+this.id+' ??')) return false;
				Dase.ajax(this.href,'delete',function(resp) {
					alert(resp);
					Dase.pageInit();
					},null,Dase.user.eid,Dase.user.htpasswd);
				return false;
				} } }
				},null);
	};
{/literal}
</script>
{/block}

{block name="content"}
<div id="contentHeader">
	<h1>Dase Collections</h1>
	<!--
	<h2>{$user->ppd}</h2>
	-->
</div>
<div id="cList"></div>

<!-- javascript template -->
<textarea class="javascript_template" id="collections_jst">
	{literal}
	<ul>
		{for c in collections}
		{if c.count < 5 }
		<li><a href="collection/${c.ascii_id}">${c.collection_name} (${c.ascii_id}) ${c.count} items</a> <a href="collection/${c.ascii_id}" id="${c.collection_name}" class="delete">[delete]</a> </li>
		{else}
		<li><a href="collection/${c.ascii_id}">${c.collection_name} (${c.ascii_id}) ${c.count} items</a></li>
		{/if}
		{/for}
	</ul>
	{/literal}
</textarea>
<!-- end javascript template -->

{/block} 


