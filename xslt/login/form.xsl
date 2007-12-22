<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:h="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="h"
  >
  <xsl:output method="xml" 
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" 
	encoding="UTF-8"/>

  <xsl:template match="insert-msg">
	<div class="alert"><xsl:value-of select="$msg"/></div>
  </xsl:template>

  <xsl:template match="insert-base-href">
	<base href="{$app_root}"/>
  </xsl:template>

  <xsl:template match="insert-return">
	<a href="{$app_root}">Return to DASe</a>	
  </xsl:template>

  <xsl:template match="@id[.='page-id']">
	<xsl:attribute name="id">
	  <xsl:value-of select="'error'"/>
	</xsl:attribute>	
  </xsl:template>

  <xsl:template match="input[@id='username-input']/@value">
	<xsl:attribute name="value">
	  <xsl:value-of select="$username"/>
	</xsl:attribute>
  </xsl:template>

  <xsl:template match="input[@id='password-input']/@value">
	<xsl:attribute name="value">
	  <xsl:value-of select="$password"/>
	</xsl:attribute>
  </xsl:template>

  <!-- Identity transformation -->
  <xsl:template match="@*|*">
	<xsl:copy>
	  <xsl:apply-templates select="@*|node()"/>
	</xsl:copy>
  </xsl:template>

</xsl:stylesheet>
