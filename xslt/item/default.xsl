<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns:atom="http://www.w3.org/2005/Atom"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:xhtml="http://www.w3.org/1999/xhtml"
  >
  <xsl:output method="xml" 
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" 
	encoding="UTF-8"/>
  <!-- include general stylesheet -->
  <xsl:include href="../site/stylesheet.xsl"/>
  <!-- use services to get any needed content -->
  <xsl:variable name="it" select="document($item)/atom:feed/atom:entry"/>

  <xsl:template match="/">
	<xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="insert-viewitem">
	<xsl:apply-templates select="$it" mode="img"/>
  </xsl:template>

  <xsl:template match="atom:entry" mode="img">
	<img src="{atom:link[@title='viewitem']/@href}" alt="file this in w/ simple title"/>
  </xsl:template>

  <xsl:template match="insert-item-metadata">
	<xsl:apply-templates select="$it/atom:content/xhtml:div/xhtml:ul/xhtml:li/xhtml:dl" mode="item-mode"/>
  </xsl:template>

  <xsl:template match="xhtml:dl" mode="item-mode">
	  <xsl:copy>
		<xsl:apply-templates/>
	  </xsl:copy>
  </xsl:template>

</xsl:stylesheet>
