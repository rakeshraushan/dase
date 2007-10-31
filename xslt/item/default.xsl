<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="xml" 
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" 
	encoding="UTF-8"/>
  <!-- include general stylesheet -->
  <xsl:include href="../site/stylesheet.xsl"/>
  <!-- use services to get any needed content -->
  <xsl:variable name="it" select="document($item)"/>

  <xsl:template match="/">
	<xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="insert-viewitem">
	<xsl:apply-templates select="$it" mode="img-mode"/>
  </xsl:template>

  <xsl:template match="insert-item-metadata">
	<xsl:apply-templates select="$it" mode="item-mode"/>
  </xsl:template>

  <xsl:template match="item" mode="item-mode">
	<dl>
	  <xsl:apply-templates select="meta"/>
	</dl>
  </xsl:template>

  <xsl:template match="item" mode="img-mode">
	<xsl:apply-templates select="media_file[@rel='viewitem']"/>
  </xsl:template>

  <xsl:template match="meta">
	<xsl:variable name="att" select="att/@ascii_id"/>
	<xsl:variable name="val_hash" select="val/@md5"/>
	<xsl:variable name="coll" select="../@collection_ascii_id"/>
	<xsl:variable name="q" select="concat('search?',$coll,':',$att,'=',$val_hash)"/>
	<dt>
	  <xsl:value-of select="att"/>
	</dt>
	<dd>
	  <a href="{$q}"><xsl:value-of select="val"/></a>
	</dd>
  </xsl:template>


  <xsl:template match="media_file[@rel='viewitem']">
	<xsl:element name="img">
	  <xsl:attribute name="src"><xsl:value-of select="@href"/></xsl:attribute>
	</xsl:element>
  </xsl:template>

</xsl:stylesheet>
