<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="xml" 
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" 
	encoding="UTF-8"/>
  <!-- include general stylesheet -->
  <xsl:include href="../site/stylesheet.xsl"/>
  <!-- use services to get any needed content -->
  <xsl:variable name="coll" select="document($collection)/collection"/>

  <xsl:template match="div[@id=browse]">
	<xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="replace_att_column">
	<div id="attColumn" class="html/{$c_ascii_id}/attributes/public"></div>
  </xsl:template>

  <xsl:template match="insert-collection-label">
	<div id="collectionAsciiId" class="{$coll/@ascii_id}"></div>
	<h2><xsl:value-of select="$coll/@name"/><xsl:text> </xsl:text><xsl:value-of select="$coll/@item_count"/> (items)</h2>
	<div id="description"><xsl:value-of select="$coll/@description"/></div>
  </xsl:template>

  <xsl:template match="insert-collection-category-links">
	<a href="html/{$coll/@ascii_id}/attributes/public" class="spill">Collection Attributes</a>
	<a href="html/{$coll/@ascii_id}/attributes/admin">Admin Attributes</a>
  </xsl:template>

  <xsl:template match="insert-collection-search-form">
	<form method="get" action="{$coll/@ascii_id}/search">
	  <input type="text" name="q" size="30"/>
	  <input type="submit" value="go" class="button"/>
	</form>
  </xsl:template>

</xsl:stylesheet>
