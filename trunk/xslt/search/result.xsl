<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns:atom="http://www.w3.org/2005/Atom"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  >
  <xsl:output method="xml" 
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" 
	encoding="UTF-8"/>
  <!-- include general stylesheet -->
  <xsl:include href="../site/stylesheet.xsl"/>
  <!-- use services to get any needed content -->
  <xsl:variable name="item-thumbs" select="document($items)/atom:feed"/>
  <!-- access data island added to source document -->
  <xsl:variable name="page" select="/html/head/dynamic"/>

  <xsl:template match="insert-item-thumbs">
	<xsl:apply-templates select="$item-thumbs/atom:entry" mode="thumbs"/>
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

  <xsl:template match="atom:entry" mode="thumbs">
	<div class="gridItem">
	  <input type="checkbox" name="img" value="{atom:id}"/>
	  <div class="image">
		<a href="{atom:id}">
		  <img src="{atom:link[@title='thumbnail']/@href}" alt="file this in w/ simple title"/>
		</a>
	  </div>
	  <div class="caption">
		<h4>
		  <xsl:value-of select="substring(atom:title,0,20)"/>
		  <xsl:if test="string-length(atom:title) &gt; 20">...</xsl:if>
		</h4>
		<h4 class="collection_name">[coll]</h4>
	  </div>
	</div>
  </xsl:template>

</xsl:stylesheet>
