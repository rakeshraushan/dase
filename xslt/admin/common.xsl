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

  <xsl:template match="/">
	<xsl:apply-templates/>
  </xsl:template>

  <xsl:variable name="user" select="html/head/dynamic/user"/>
  <xsl:variable name="collection" select="html/head/dynamic/collection"/>

  <xsl:template match="insert-base-href">
	<base href="{$app_root}"/>
  </xsl:template>

  <xsl:template match="insert-page-hook">
	<xsl:value-of select="$page_hook"/>
  </xsl:template>

  <xsl:template match="insert-collection-name">
	<p id="collection_name"><xsl:value-of select="$collection/collection_name"/></p>
	<div class="hide" id="collection_ascii_id"><xsl:value-of select="$collection/ascii-id"/></div>
  </xsl:template>

  <xsl:template match="insert-breadcrumbs">
	<a href="{$app_root}">DASe</a> :: <a href="manage">Manage</a>	
  </xsl:template>

  <!-- Identity transformation -->
  <xsl:template match="@*|*">
	<xsl:copy>
	  <xsl:apply-templates select="@*|node()"/>
	</xsl:copy>
  </xsl:template>

</xsl:stylesheet>
