<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns:h="http://www.w3.org/1999/xhtml"
  xmlns:atom="http://www.w3.org/2005/Atom"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  >
  <xsl:output method="xml" indent="yes" encoding="UTF-8"/>
  <xsl:preserve-space elements="*"/>
  <!-- include general stylesheet -->
  <xsl:include href="../site/stylesheet.xsl"/>
  <!-- use services to get any needed content -->
  <xsl:variable name="items" select="document($atom)/atom:feed"/>
  <!-- note that column numbers are hard-coded in 2 places below
  where they cannot be included in a predicate in a match-->
  <xsl:variable name="columns" select="5"/>

  <xsl:template match="insert-item-thumbs">
	<h1><xsl:value-of select="$items/atom:subtitle/text()"/></h1>
	<div class="pageControls">
	  <a href="{$items/atom:link[@rel='previous']/@href}">prev</a> |
	  <a href="{$items/atom:link[@rel='next']/@href}">next</a> 
	</div>
	<table>
	  <xsl:apply-templates select="$items/atom:entry" mode="items"/>
	</table>
	<!-- we just need a place to stash the current url so our refine code can parse it -->
	<div id="self_url" class="hide"><xsl:value-of select="$items/atom:link[@rel='self']/@href"/></div>
  </xsl:template>

  <xsl:template match="insert-collection-name">
	<!--
	<h2><xsl:apply-templates select="$page/search/collection"/></h2>
	-->
  </xsl:template>

  <xsl:template match="collection">
	<a href="{$app_root}{@ascii_id}"><xsl:value-of select="@name"/></a> (<xsl:value-of select="$total"/> items)
  </xsl:template>

  <xsl:template match="insert-tallies">
	<div id="search_tallies">
	  <h3>Search Results by Collection</h3>
	  <!--the link to tallies is in the atom document-->
	  <xsl:copy-of select="document($items/atom:link[@rel='http://daseproject.org/relation/search-tallies']/@href)/h:html/h:body/h:p/*"/>
	</div>
  </xsl:template>

  <xsl:template match="insert-links">
	<!--
	<a href="{$page/search/prev}">prev</a> | 
	<a href="{$page/search/next}">next</a>  
	-->
  </xsl:template>

  <!-- from http://www.jguru.com/faq/view.jsp?EID=1094766 (xslt html tables)-->
  <!-- note per Kay p.441 predicates in match cannot include variables ugh -->
  <xsl:template match="atom:entry[(position()-1) mod 5 != 0]" mode="items">
	<!-- /dev/null -->
  </xsl:template>

  <xsl:template match="atom:entry[(position()-1) mod 5 = 0]" mode="items">
	<xsl:variable name="coll" select="concat($app_root,'categories/collection')"/>
	<xsl:variable name="index" select="concat($app_root,'categories/search_result/index')"/>
	<xsl:variable name="item_id" select="concat($app_root,'categories/item_id')"/>
	<xsl:text>
	</xsl:text>
	<tr>
	  <xsl:text>
	  </xsl:text>
	  <td>
		<div class="checkNum">
		  <input type="checkbox" name="item_id" value="{atom:category[@scheme=$item_id]/@term}"/>
		  <xsl:value-of select="atom:category[@scheme='http://daseproject.org/category/search_result/index']/@label"/><xsl:text>.</xsl:text>
		</div>
		<div class="image">
		  <a href="{atom:link[@rel='http://daseproject.org/relation/search-item-link']/@href}">
			<img src="{atom:link[@rel='http://daseproject.org/relation/media/thumbnail']/@href}" alt="{atom:title/text()}"/>
		  </a>
		</div>
		<div class="caption">
		  <h4>
			<xsl:value-of select="substring(atom:title,0,80)"/>
			<xsl:if test="string-length(atom:title) &gt; 80">...</xsl:if>
		  </h4>
		  <h4 class="collection_name"><xsl:value-of select="atom:category[@scheme=$coll]/@label"/></h4>
		</div>

	  </td>
	  <xsl:for-each select="following-sibling::atom:entry[position() &lt; $columns]">
		<xsl:text>
		</xsl:text>
		<td>
		  <div class="checkNum">
			<input type="checkbox" name="img" value="{atom:id}"/>
			<xsl:value-of select="atom:category[@scheme='http://daseproject.org/category/search_result/index']/@label"/><xsl:text>.</xsl:text>
		  </div>
		  <div class="image">
			<a href="{atom:link[@rel='http://daseproject.org/relation/search-item-link']/@href}">
			  <img src="{atom:link[@rel='http://daseproject.org/relation/media/thumbnail']/@href}" alt="{atom:title/text()}"/>
			</a>
		  </div>
		  <div class="caption">
			<h4>
			  <xsl:value-of select="substring(atom:title,0,80)"/>
			  <xsl:if test="string-length(atom:title) &gt; 80">...</xsl:if>
			</h4>
			<h4 class="collection_name"><xsl:value-of select="atom:category[@scheme=$coll]/@label"/></h4>
		  </div>
		</td>
		<!-- this will fill out blank cells in table-->
		<xsl:if test="position() = last() and last() + 1 != $columns">
		  <td colspan="0" class="blank">
			<xsl:text> </xsl:text>
		  </td>
		</xsl:if>
	  </xsl:for-each>
	  <!-- this will fill out blank cells in table-->
	  <xsl:if test="position() = last()">
		<td colspan="0" class="blank">
		  <xsl:text>  </xsl:text>
		</td>
	  </xsl:if>
	</tr>
  </xsl:template>
</xsl:stylesheet>
