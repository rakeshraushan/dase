<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns:atom="http://www.w3.org/2005/Atom"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  >
  <!-- include general stylesheet -->
  <xsl:include href="../site/stylesheet.xsl"/>
  <!-- use services to get any needed content -->
  <xsl:variable name="items" select="document($atom)/atom:feed"/>
  <!-- access data island added to source document -->
  <xsl:variable name="page" select="/html/head/dynamic"/>

  <xsl:template match="insert-item-thumbs">
	<table>
		<xsl:apply-templates select="$items/atom:entry" mode="items"/>
	</table>
  </xsl:template>

  <xsl:template match="insert-collection-name">
	<h2><xsl:apply-templates select="$page/search/collection"/></h2>
  </xsl:template>

  <xsl:template match="collection">
	<a href="{$app_root}{@ascii_id}"><xsl:value-of select="@name"/></a> (<xsl:value-of select="$total"/> items)
  </xsl:template>

  <xsl:template match="insert-tallies">
	<h3>Search Results per Collection:</h3>
	<ul>
	  <xsl:apply-templates select="$page/search/tallies"/>
	</ul>
	<!--
	<pre><xsl:apply-templates select="$page/search/sql"/></pre>
	<pre><xsl:apply-templates select="$page/search/search"/></pre>
	-->
	<pre><xsl:apply-templates select="$page/search/echo"/></pre>
	<!--<xsl:value-of select="$items"/>-->
  </xsl:template>

  <xsl:template match="insert-links">
	<a href="{$page/search/prev}">prev</a> | 
	<a href="{$page/search/next}">next</a>  
  </xsl:template>

  <xsl:template match="tally">
	<li><xsl:value-of select="@collection_name"/><xsl:text> (</xsl:text><xsl:value-of select="@total"/><xsl:text>)</xsl:text></li>
  </xsl:template>

  <xsl:template match="atom:entry[(position()-1) mod 5 != 0]" mode="items">
  </xsl:template>

  <!-- from http://www.jguru.com/faq/view.jsp?EID=1094766 (xslt html tables)-->
  <xsl:template match="atom:entry[(position()-1) mod 5 = 0]" mode="items">
	<xsl:variable name="coll" select="concat($app_root,'categories/collection')"/>
	<xsl:variable name="index" select="concat($app_root,'categories/search_result/index')"/>
	<xsl:variable name="item_id" select="concat($app_root,'categories/item_id')"/>
	<tr>
	  <td>
		  <div class="checkNum">
			<input type="checkbox" name="item_id" value="{atom:category[@scheme=$item_id]/@term}"/>
			<xsl:value-of select="atom:category[@scheme=$index]/@label"/><xsl:text>.</xsl:text>
		  </div>
		  <div class="image">
			<a href="{atom:id}">
			  <img src="{atom:link[@rel='thumbnail']/@href}" alt="file this in w/ simple title"/>
			</a>
		  </div>
		  <div class="caption">
			<h4>
			  <xsl:value-of select="substring(atom:title,0,20)"/>
			  <xsl:if test="string-length(atom:title) &gt; 20">...</xsl:if>
			</h4>
			<h4 class="collection_name"><xsl:value-of select="atom:category[@scheme=$coll]/@label"/></h4>
		  </div>

	  </td>
	  <xsl:for-each select="following-sibling::atom:entry[position() &lt; 5]">
		<td>
		  <div class="checkNum">
			<input type="checkbox" name="img" value="{atom:id}"/>
			<xsl:value-of select="atom:category[@scheme=$index]/@label"/><xsl:text>.</xsl:text>
		  </div>
		  <div class="image">
			<a href="{atom:id}">
			  <img src="{atom:link[@rel='thumbnail']/@href}" alt="file this in w/ simple title"/>
			</a>
		  </div>
		  <div class="caption">
			<h4>
			  <xsl:value-of select="substring(atom:title,0,20)"/>
			  <xsl:if test="string-length(atom:title) &gt; 20">...</xsl:if>
			</h4>
			<h4 class="collection_name"><xsl:value-of select="atom:category[@scheme=$coll]/@label"/></h4>
		  </div>
		</td>
	  </xsl:for-each>
	</tr>
  </xsl:template>
</xsl:stylesheet>
