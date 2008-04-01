<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns:atom="http://www.w3.org/2005/Atom"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:h="http://www.w3.org/1999/xhtml"
  xmlns:d="http://daseproject.org"
  xmlns:os="http://a9.com/-/spec/opensearch/1.1/"
  xmlns:php="http://php.net/xsl"
  xsl:extension-element-prefixes="php"
  exclude-result-prefixes="atom h d php"
  >
  <xsl:import href="common.xsl"/> 
  <xsl:output method="xml" indent="yes"
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" 
	encoding="UTF-8"/>


  <xsl:template match="insert-content">
	<div class="full" id="browse">
	  <div id="msg" class="alert hide"></div>
	  <div id="contentHeader">
		<!-- SEARCH FORM -->
		<form id="searchCollectionsDynamic" method="get" action="search">
		  <div>
			<input id="queryInput" type="text" name="q" size="30"/>
			<input type="submit" value="Search" class="button"/>
			<select id="collectionsSelect" name="collection_ascii_id">
			</select>
			<span id="preposition" class="hide">in</span>
			<select id="attributesSelect" class="hide">
			</select>
			<input id="refineCheckbox" type="checkbox"/>refine current result
		  </div>
		  <div id="refinements"/>
		</form>
		<h3><xsl:value-of select="$items/atom:subtitle/h:div/h:div[@class='searchEcho']"/></h3>
		<h4>
		  <a href="{$items/atom:link[@rel='previous']/@href}">prev</a> |
		  <a href="{$items/atom:link[@rel='next']/@href}">next</a> 
		</h4>
	  </div> <!--close contentHeader -->
	  <form id="saveToForm" method="post" action="save">	
		<table id="itemSet">
		  <xsl:apply-templates select="$items/atom:entry" mode="items">
			<!-- pass along the opensearch startIndex -->
			<xsl:with-param name="startIndex" select="$items/os:startIndex"/>
		  </xsl:apply-templates>
		</table>
		<a href="" id="checkall">check/uncheck all</a>
		<div id="saveChecked"></div>
	  </form>
	  <div class="spacer"/>
	</div>
	<div class="full" id="searchTallies">
	  <h3>Search Results by Collection</h3>
	  <!--the link to tallies is in the atom document-->
	  <xsl:copy-of select="$items/atom:subtitle/h:div/h:ul"/>
	</div>
	<!-- we just need a place to stash the current url so our refine code can parse it -->
	<div id="self_url" class="data"><xsl:value-of select="translate($items/atom:link[@rel='self']/@href,'+',' ')"/></div>
  </xsl:template>

</xsl:stylesheet>
