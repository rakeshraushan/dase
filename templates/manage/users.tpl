{extends file="manage/layout.tpl"}

{block name="head"}
<script type="text/javascript">
	{literal}
	Dase.pageInit = function() {
		var url = Dase.base_href+'manage/users.json';
		Dase.getJSON(url,function(json){
				var data = { 'users': json };
				var templateObj = TrimPath.parseDOMTemplate("users_jst");
				Dase.$('userList').innerHTML = templateObj.process(data);
				},null,Dase.$('userList').className);
	};
{/literal}
</script>
{/block}

{block name="content"}
<div id="contentHeader">
	<h1>Dase Users</h1>
	<!--
	<h2>{$user->ppd}</h2>
	-->
</div>
<div id="userList" class="limit={$limit}"></div>

<!-- javascript template -->
<textarea class="javascript_template" id="users_jst">
	{literal}
	<ul>
		{for u in users}
		<li><a href="manage/user/${u.eid}">${u.eid}</a> ${u.name}</li>
		{/for}
	</ul>
	{/literal}
</textarea>
<!-- end javascript template -->

{/block} 


