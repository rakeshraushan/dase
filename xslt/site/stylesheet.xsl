<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:h="http://www.w3.org/1999/xhtml"
  xmlns:php="http://php.net/xsl"
  xsl:extension-element-prefixes="php"
  exclude-result-prefixes="h"
  >
  <xsl:output method="xml" 
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" 
	encoding="UTF-8"/>

  <xsl:variable name="local" select="document($local-layout)/source"/>

  <xsl:template match="/">
	<xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="insert-base-href">
	<base href="{$app_root}"/>
  </xsl:template>

  <xsl:template match="insert-title">
	<xsl:apply-templates select="$local/title"/>
  </xsl:template>

  <xsl:template match="insert-content">
	<xsl:apply-templates select="$local/content/*"/>
  </xsl:template>

  <xsl:template match="dynamic"/>

  <xsl:template match="insert-timer">
	<!--<xsl:value-of select="$timer"/>-->	
	<!--	<xsl:value-of select="php:functionString('Dase_Timer::getElapsed')"/>-->	
  </xsl:template>

  <!-- Identity transformation -->
  <xsl:template match="@*|*">
	<xsl:copy>
	  <xsl:apply-templates select="@*|node()"/>
	</xsl:copy>
  </xsl:template>

</xsl:stylesheet>
