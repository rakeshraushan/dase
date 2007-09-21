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
<li><a href="manage/stats">stats</a></li>
</ul>

</div>


<div class="center">

{if $top_ten}
<div class="topTen">
<h1>Top Collections: most used* images</h1>
<dl>
{foreach from=$top_ten item=num key=name}
<dt>{$name}</dt>
<dd>{$num}</dd>
{/foreach}
</dl>
<h5>*included in a cart, user collection, or slideshow</h5>
</div>
<br/>&nbsp;<br/>
<div class="topTen">
<h1>Top Collections: by size</h1>
<dl>
{foreach from=$by_size item=num key=name}
<dt>{$name}</dt>
<dd>{$num}</dd>
{/foreach}
</dl>
</div>
{/if}


{if $routes}
{foreach from=$routes key=method item=sroutes}
<h3>{$method} method</h3>
<dl class="routes">
{foreach from=$sroutes key=k item=v}
<dt>/{$k}/</dt>
{foreach from=$v key=name item=value}
<dd>[{$name}] {$value}</dd>
{/foreach}
{/foreach}
</dl>
{/foreach}
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


