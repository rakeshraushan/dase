{extends file="layout.tpl"}

{block name="content"}
<div id="home">
{if $request->is_superuser}
<form method="post">
	{assign var=rows value=$home->content|count_words}
	<textarea rows="{$rows/8}" name="home_text">{$home->content}</textarea>
	<input type="submit" value="update">
	<input type="submit" name="cancel" value="cancel">
</form>
<pre>
# Header 1 #
## Header 2 ##
### Header 3 ###             (Hashes on right are optional)
#### Header 4 ####
##### Header 5 #####

This is a paragraph, which is text surrounded by whitespace.
Paragraphs can be on one line (or many), and can drone on
for hours.  

Here is a Markdown link to [Warped](http://warpedvisions.org), 
and a literal <http://link.com/>.  Now some SimpleLinks, like 
one to <a rel="tag" target="_new" href="http://google.com/search?q=google&amp;btnI=">google</a> (autolinks to are-you-feeling-lucky), a <a rel="tag" target="_new" href="http://en.wikipedia.org/wiki/Test">test</a> 
link to a Wikipedia page, and a <a rel="tag" target="_new" href="http://foldoc.doc.ic.ac.uk/foldoc/foldoc.cgi?query=cpu&amp;action=Search">CPU</a> at foldoc. 

Now some inline markup like _italics_,  **bold**, and `code()`.

![picture alt](/images/photo.jpeg "Title is optional")     

> Blockquotes are like quoted text in email replies
>> And, they can be nested

* Bullet lists are easy too
- Another one
+ Another one

1. A numbered list
2. Which is numbered
3. With periods and a space

And now some code:

    // Code is just text indented a bit
    which(is_easy) to_remember();

Text with  
two trailing spaces  
(on the right)  
can be used  
for things like poems  

Some horizontal rules ...

* * * *
****
--------------------------
</pre>
<h3>[markdown cheatsheet from http://warpedvisions.org/projects/markdown-cheat-sheet/]</h3>

{/if}
</div>
{/block}

