{extends file="admin/layout.tpl"}
{block name="content"}
<form action="admin/docs" method="get">
	<select name="class_id">
		<option value="">select a class</option>
		{foreach item=classname from=$class_list key=id}
		<option value="{$id}" {if $id eq $class_id}selected="selected"{/if}>{$classname}</option>
		{/foreach}
	</select>
	<input type="submit" value="view class documentation"/>
</form>
<div id="classInfo">
	{if $doc}
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
		<li>
		{assign var=decl value=$method_info->getDeclaringClass()}
		<span class="declaring">[{$decl->getName()}]</span>
		<span class="mods">{$doc|modifiers:$method_info}</span> 
		<span class="method">{$meth}</span> 
		(
		{foreach item=param name=param_set from=$method_info->getParameters()}
		{$param|params}
		{if $smarty.foreach.param_set.last}
		{else}
		,
		{/if}
		{/foreach}
		)
		{assign var=comment value=$method_info->getDocComment()}
		{if $comment}
		<p class="comment">{$comment}</p>
		{/if}
		</li>
		{/foreach}
	</ul>
	<h2>Protected Methods</h2>
	<ul>
		{foreach key=meth item=method_info from=$doc->protectedMethods}
		<li>
		{assign var=decl value=$method_info->getDeclaringClass()}
		<span class="declaring">[{$decl->getName()}]</span>
		<span class="mods">{$doc|modifiers:$method_info}</span> 
		<span class="method">{$meth}</span> 
		(
		{foreach item=param name=param_set from=$method_info->getParameters()}
		{$param|params}
		{if $smarty.foreach.param_set.last}
		{else}
		,
		{/if}
		{/foreach}
		)
		{assign var=comment value=$method_info->getDocComment()}
		{if $comment}
		<p class="comment">{$comment}</p>
		{/if}
		</li>
		{/foreach}
	</ul>
	<h2>Private Methods</h2>
	<ul>
		{foreach key=meth item=method_info from=$doc->privateMethods}
		<li>
		{assign var=decl value=$method_info->getDeclaringClass()}
		<span class="declaring">[{$decl->getName()}]</span>
		<span class="mods">{$doc|modifiers:$method_info}</span> 
		<span class="method">{$meth}</span> 
		(
		{foreach item=param name=param_set from=$method_info->getParameters()}
		{$param|params}
		{if $smarty.foreach.param_set.last}
		{else}
		,
		{/if}
		{/foreach}
		)
		{assign var=comment value=$method_info->getDocComment()}
		{if $comment}
		<p class="comment">{$comment}</p>
		{/if}
		</li>
		{/foreach}
	</ul>
	<h2>Public Members</h2>
	<ul>
		{foreach key=mem item=mem_info from=$doc->publicDataMembers}
		<li>
		{assign var=decl value=$mem_info->getDeclaringClass()}
		<span class="declaring">[{$decl->getName()}]</span>
		<span class="mods">{$doc|modifiers:$mem_info}</span> 
		{if $default_properties.$mem|@is_array}
		{$mem} = array(); 
		{else}
		{$mem}; 
		{/if}
		</li>
		{/foreach}
	</ul>
	<h2>Protected Members</h2>
	<ul>
		{foreach key=mem item=mem_info from=$doc->protectedDataMembers}
		<li>
		{assign var=decl value=$mem_info->getDeclaringClass()}
		<span class="declaring">[{$decl->getName()}]</span>
		<span class="mods">{$doc|modifiers:$mem_info}</span> 
		{if $default_properties.$mem|@is_array}
		{$mem} = array(); 
		{else}
		{$mem}; 
		{/if}
		</li>
		{/foreach}
	</ul>
	<h2>Private Members</h2>
	<ul>
		{foreach key=mem item=mem_info from=$doc->privateDataMembers}
		<li>
		{assign var=decl value=$mem_info->getDeclaringClass()}
		<span class="declaring">[{$decl->getName()}]</span>
		<span class="mods">{$doc|modifiers:$mem_info}</span> 
		{if $default_properties.$mem|@is_array}
		{$mem} = array(); 
		{else}
		{$mem}; 
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
	{/if}
</div>
{/block}
