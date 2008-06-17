<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title>DASe Docs</title>
		<base href="{$app_root}"/>
		<link rel="stylesheet" type="text/css" href="www/css/yui.css"/>
		<link rel="stylesheet" type="text/css" href="www/css/style.css"/>
		<link rel="stylesheet" type="text/css" href="www/css/manage.css"/>
		<link rel="shortcut icon" href="www/images/favicon.ico"/>
	</head>

	<body>
		<div id="manageHeader">
			<p>DASe Class Documentation</p>
		</div>

		<div id="sidebar">
			<ul id="class_list">
				{foreach item=classname from=$class_list key=id}
				<li><a href="manage/docs/{$id}">{$classname}</a></li>
				{/foreach}
			</ul>
		</div>
		<div id="content">
			{assign var=defaults value=$doc->defaultproperties}
			<div id="classInfo">
				<h1>{$doc->name}</h1>
				<p>
				<h3>Date: {$smarty.now|date_format}</h3>
				<h3>PHP Version: {$phpversion}</h3>
				<h3>Type: {$doc->classType}</h3>
				<p>{$doc->fullDescription}</p>
				</p>
				<h2>Public Methods</h2>
				<ul>
					{foreach key=meth item=method_info from=$doc->publicMethods}
					<li><span class="mods">{$doc|modifiers:$method_info}</span> 
					{$meth} 
					(
					{foreach item=param name=param_set from=$method_info->getParameters()}
					{$param|params}
					{if $smarty.foreach.param_set.last}
					{else}
					,
					{/if}
					{/foreach}
					)
					</li>
					{/foreach}
				</ul>
				<h2>Protected Methods</h2>
				<ul>
					{foreach key=meth item=method_info from=$doc->protectedMethods}
					<li><span class="mods">{$doc|modifiers:$method_info}</span> 
					{$meth} 
					(
					{foreach item=param name=param_set from=$method_info->getParameters()}
					{$param|params}
					{if $smarty.foreach.param_set.last}
					{else}
					,
					{/if}
					{/foreach}
					)
					</li>
					{/foreach}
				</ul>
				<h2>Private Methods</h2>
				<ul>
					{foreach key=meth item=method_info from=$doc->privateMethods}
					<li><span class="mods">{$doc|modifiers:$method_info}</span> 
					{$meth} 
					(
					{foreach item=param name=param_set from=$method_info->getParameters()}
					{$param|params}
					{if $smarty.foreach.param_set.last}
					{else}
					,
					{/if}
					{/foreach}
					)
					</li>
					{/foreach}
				</ul>
				<h2>Public Members</h2>
				<ul>
					{foreach key=mem item=mem_info from=$doc->publicDataMembers}
					<li><span class="mods">{$doc|modifiers:$mem_info}</span> 
					{if $default_properties.$mem|@is_array}
					{$mem} = array(); 
					{else}
					{$mem} = {$default_properties.$mem}; 
					{/if}
					</li>
					{/foreach}
				</ul>
				<h2>Protected Members</h2>
				<ul>
					{foreach key=mem item=mem_info from=$doc->protectedDataMembers}
					<li><span class="mods">{$doc|modifiers:$mem_info}</span> 
					{if $default_properties.$mem|@is_array}
					{$mem} = array(); 
					{else}
					{$mem} = {$default_properties.$mem}; 
					{/if}
					</li>
					{/foreach}
				</ul>
				<h2>Private Members</h2>
				<ul>
					{foreach key=mem item=mem_info from=$doc->privateDataMembers}
					<li><span class="mods">{$doc|modifiers:$mem_info}</span> 
					{if $default_properties.$mem|@is_array}
					{$mem} = array(); 
					{else}
					{$mem} = {$default_properties.$mem}; 
					{/if}
					</li>
					{/foreach}
				</ul>
				<h2>Constants</h2>
				<ul>
					{foreach key=key item=value from=$doc->constants}
					<li><span class="mods">{$key}</span> 
					{$value}
					</li>
					{/foreach}
				</ul>
			</div>
		</div>

		<div class="spacer"/>

			<div id="footer">
				<a href="apps/help" id="helpModule">FAQ</a> | 
				<a href="mailto:dase@mail.laits.utexas.edu">email</a> | 
				<a href="copyright">Copyright/Usage Statement</a> | 
				<!--
				<insert-timer/> seconds |
				-->
				<img src="www/images/dasepowered.png" alt="DASePowered icon"/>
			</div><!--closes footer-->
			<div id="debugData" class="pagedata"></div>
		</div>
	</body>
</html>
