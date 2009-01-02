<!-- javascript template -->
<textarea class="javascript_template" id="atom_jst">
	{literal}
	<entry xmlns="http://www.w3.org/2005/Atom">
	<id>${atom.id}</id>
	<title>${atom.title}</title>
	<author><name>${atom.author_name}</name></author>
	{if atom.summary}
	<summary>${atom.summary}</summary>
	{/if}
	<rights>${atom.rights}</rights>
	<updated>${atom.updated}</updated>
	<category term="${atom.entrytype}" scheme="http://daseproject.org/category/entrytype"/>
	<content type="${atom.content.type}">${atom.content.text}</content>
	{for c in atom.category}
	{if c.value}
	<category term="${c.term}" scheme="${c.scheme}" label="${c.label}">${c.value}</category>
	{else}
	<category term="${c.term}" scheme="${c.scheme}"
	{if c.label} label="${c.label}" {/if}/>
	{/if}
	{/for}
	{for l in atom.link}
	<link rel="${l.rel}" href="${l.href}" 
	{if l.type} type="${l.type}" {/if}
	{if l.length} length="${l.length}" {/if}
	{if l.title} title="${l.title}" {/if}/>
	{/for}
	</entry>
	{/literal}
</textarea>
<!-- end javascript template -->