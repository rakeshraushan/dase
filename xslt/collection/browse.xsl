<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns:h="http://www.w3.org/1999/xhtml"
  xmlns:atom="http://www.w3.org/2005/Atom"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  >
  <!-- include general stylesheet -->
  <xsl:include href="../site/stylesheet.xsl"/>
  <!-- use services to get any needed content -->
  <xsl:variable name="coll" select="document($src)/atom:feed"/>

  <xsl:template match="div[@id=browse]">
	<xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="replace_att_column">
	<div id="attColumn" class="html/{$coll/atom:category[@scheme='http://daseproject.org/category/collection/ascii_id']/@term}/attributes/public"></div>
  </xsl:template>

  <xsl:template match="insert-collection-label">
	<div id="collectionAsciiId" class="{$coll/atom:category[@scheme='http://daseproject.org/category/collection/ascii_id']/@term}"></div>
	<h2><xsl:value-of select="$coll/atom:title/text()"/>
	  <xsl:text> (</xsl:text><xsl:value-of select="$coll/atom:category[@scheme='http://daseproject.org/category/collection/item_count']/@term"/> items)</h2>
	<div id="description"><xsl:value-of select="$coll/@description"/></div>
  </xsl:template>

  <xsl:template match="insert-collection-category-links">
	<a href="html/{$coll/atom:category[@scheme='http://daseproject.org/category/collection/ascii_id']/@term}/attributes/public" class="spill">Collection Attributes</a>
	<a href="html/{$coll/atom:category[@scheme='http://daseproject.org/category/collection/ascii_id']/@term}/attributes/admin">Admin Attributes</a>
  </xsl:template>

  <xsl:template match="insert-collection-search-form">
	<form method="get" action="{$coll/atom:category[@scheme='http://daseproject.org/category/collection/ascii_id']/@term}/search">
	  <input type="text" name="q" size="30"/>
	  <input type="submit" value="go" class="button"/>
	</form>
  </xsl:template>

</xsl:stylesheet>
