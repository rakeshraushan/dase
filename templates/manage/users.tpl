{extends file="manage/layout.tpl"}

{block name="javascript"}
{literal}
Dase.pageInit = function() {
Dase.getJSON(Dase.base_href + "manage/users.json",function(json){
var data = { 'users': json };
var templateObj = TrimPath.parseDOMTemplate("users_jst");
Dase.$('userList').innerHTML = templateObj.process(data);
});
};
{/literal}
{/block}

{block name="content"}
<div id="contentHeader">
	<h1>Dase Users</h1>
	<!--
	<h2>{$user->ppd}</h2>
	-->
</div>
<div id="userList"></div>
{literal}
<!-- javascript template -->
<textarea class="javascript_template" id="users_jst">
	<ul>
		{for u in users}
		<li><a href="manage/user/${u.eid}">${u.eid}</a> ${u.name}</li>
		{/for}
	</ul>
</textarea>
{/literal}
{/block} 


