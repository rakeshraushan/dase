<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns:atom="http://www.w3.org/2005/Atom"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:xhtml="http://www.w3.org/1999/xhtml"
  xmlns:d="http://daseproject.org"
  >
  <xsl:output method="xml" 
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" 
	encoding="UTF-8"/>
  <!-- include general stylesheet -->
  <xsl:include href="../site/stylesheet.xsl"/>
  <!-- use services to get any needed content -->
  <xsl:variable name="it" select="document($atom)/atom:feed"/>

  <xsl:template match="/">
	<xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="insert-viewitem">
	<xsl:apply-templates select="$it/atom:entry" mode="img"/>
	<h4>Media:</h4>
	<ul id="mediaLinks">
	  <xsl:apply-templates select="$it/atom:entry/atom:link" mode="media"/>
	</ul>
  </xsl:template>

  <xsl:template match="atom:entry" mode="img">
	<img src="{atom:link[@rel='http://daseproject.org/relation/media/viewitem']/@href}" width="{atom:link[@rel='http://daseproject.org/relation/media/viewitem']/@d:width}" height="{atom:link[@rel='http://daseproject.org/relation/media/viewitem']/@d:height}" alt="{atom:title/text()}"/>
  </xsl:template>

  <xsl:template match="atom:link[@d:size]" mode="media">
	<li><a href="{@href}"><xsl:value-of select="@d:size"/> (<xsl:value-of select="@d:width"/>x<xsl:value-of select="@d:height"/>)</a></li>
  </xsl:template>

  <xsl:template match="insert-search-echo">
	<h1><xsl:value-of select="$it/atom:subtitle/text()"/></h1>
  </xsl:template>

  <xsl:template match="insert-prev-next">
	<div class="pageControls">
	  <a href="{$it/atom:link[@rel='previous']/@href}">prev</a> |
	  <a href="{$it/atom:entry/atom:link[@rel='http://daseproject.org/relation/search-link']/@href}">up</a> |
	  <a href="{$it/atom:link[@rel='next']/@href}">next</a> 
	</div>
  </xsl:template>

  <xsl:template match="insert-item-metadata">
	<xsl:apply-templates select="$it/atom:entry/atom:content/xhtml:div/xhtml:dl" mode="item-mode"/>
  </xsl:template>

  <xsl:template match="xhtml:dl" mode="item-mode">
	<xsl:copy>
	  <xsl:apply-templates/>
	</xsl:copy>
  </xsl:template>

</xsl:stylesheet>
