<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html>
<head>
<title>Manage DASe</title>
<base href="{$app_root}/"/>
<link rel="stylesheet" href="css/manage.css" type="text/css">
<link rel="home" href="/" title="DASe">
</head>

<body>
<div class="topbar">
<a href="/dase">DASe</a> :: <a href="manage">Manage DASe</a> 
{if $breadcrumb_url}
:: <a href="{$breadcrumb_url}">{$breadcrumb_name}</a>
{/if}
</div>

<div class="sidebar">

<ul class="tools">
<li><a href="manage/routes">route mappings</a></li>
<li><a href="manage/log/standard">standard log</a></li>
<li><a href="manage/log/sql">sql log</a></li>
<li><a href="manage/log/error">error log</a></li>
<li><a href="manage/modules">modules</a></li>
</ul>

</div>


<div class="center">

{if $routes}
<dl class="routes">
{foreach from=$routes key=k item=v}
<dt>/{$k}/</dt>
{foreach from=$v key=name item=value}
<dd>[{$name}] {$value}</dd>
{/foreach}
{/foreach}
</dl>
{/if}
{if 'standard' == $log_name}
<ul><li>{$log|nl2br}</li></ul>
{/if}
{if 'sql' == $log_name}
<ul><li>{$log|nl2br}</li></ul>
{/if}
{if 'error' == $log_name}
<ul><li>{$log|nl2br}</li></ul>
{/if}
{if $modules}
<ul>
{foreach from=$modules key=k item=v}
<li><strong>{$k}</strong> ({$v})</li>
{/foreach}
</ul>
{/if}

</ul>

</div>

</body>
</html>


