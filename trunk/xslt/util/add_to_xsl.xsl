<?xml version="1.0" encoding="UTF-8"?>
??????????????????????????????????????????????????????????
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="xml" 
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" 
	encoding="UTF-8"/>

  <xsl:template match="/">
	<xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="insert-base-href">
	<base href="{$app_root}"/>
  </xsl:template>

  <xsl:template match="insert-title">
	<xsl:apply-templates select="document($layout)/html/head/title"/>
  </xsl:template>

  <xsl:template match="insert-content">
	<xsl:apply-templates select="document($layout)/html/body/*"/>
  </xsl:template>

  <xsl:template match="insert-timer">
	<xsl:value-of select="$timer"/>	
  </xsl:template>

  <!-- Identity transformation -->
  <xsl:template match="@*|*">
	<xsl:copy>
	  <xsl:apply-templates select="@*|node()"/>
	</xsl:copy>
  </xsl:template>

</xsl:stylesheet>
