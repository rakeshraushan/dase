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

<!-- javascript template -->
<textarea class="javascript_template" id="atom_display_jst">
	{literal}
	<h2>elements</h2>
	<table class="atom list">
		<tr>
			<th>id</th>
			<td>${atom.id}</td>
			</tr><tr>
			<th>title</th>
			<td>${atom.title}</td>
			</tr><tr>
			<th>author/name</th>
			<td>${atom.author_name}</td>
			</tr><tr>
			<th>summary</th>
			<td>${atom.summary}</td>
			</tr><tr>
			<th>rights</th>
			<td>${atom.rights}</td>
			</tr><tr>
			<th>updated</th>
			<td>${atom.updated}</td>
			</tr><tr>
			<th>content@type</th>
			<td>${atom.content.type}</td>
			</tr><tr>
			<th>content</th>
			<td>${atom.content.text}</td>
			</tr><tr>
			<th>entrytype</th>
			<td>${atom.entrytype}</td>
		</tr>
	</table>
	<h2>categories</h2>
	<table class="atom">
		<tr>
			<th>term</th>
			<th>scheme</th>
			<th>label</th>
			<th>value</th>
		</tr>
		{for c in atom.category}
		<tr>
			<td>${c.term}</td>
			<td>${c.scheme}</td>
			<td>${c.label}</td>
			<td>${c.value}</td>
		</tr>
		{/for}
	</table>
	<h2>links</h2>
	<table class="atom">
		<tr>
			<th>rel</th>
			<th>href</th>
			<th>type</th>
			<th>title</th>
			<th>length</th>
		</tr>
		{for l in atom.link}
		<tr>
			<td>${l.rel}</td>
			<td>${l.href}</td>
			<td>${l.type}</td>
			<td>${l.title}</td>
			<td>${l.length}</td>
		</tr>
		{/for}
	</table>
	{/literal}
</textarea>
<!-- end javascript template -->
