<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:h="http://www.w3.org/1999/xhtml"
  xmlns:atom="http://www.w3.org/2005/Atom"
  xmlns:php="http://php.net/xsl"
  xsl:extension-element-prefixes="php"
  exclude-result-prefixes="h"
  >
  <xsl:output method="xml" indent="yes"
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" 
	encoding="UTF-8"/>

  <xsl:variable name="m_atts" select="html/head/dynamic/media_attribute"/>


  <xsl:template match="/">
	<xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="insert-page-hook">
	<xsl:value-of select="$page_hook"/>
  </xsl:template>

  <xsl:template match="insert-base-href">
	<base href="{$app_root}"/>
  </xsl:template>

  <xsl:template match="insert-title">
	<title>Media Attributes</title>
  </xsl:template>

  <xsl:template match="insert-content">
	<div class="list" id="browse">
	  <xsl:call-template name="insert-msg"/>
	  <h1>Media File Attributes</h1>
	  <xsl:apply-templates select="$m_atts"/>
	</div>
  </xsl:template>

  <xsl:template match="dynamic"/>

  <xsl:template match="media_attribute">
	<form method="post" action="media/attribute/{@id}" class="adminForm">
	  <div>
		<label for="term">term</label>
		<input type="text" name="term" value="{term}"/>
	  </div>
	  <div>
		<label for="label">label</label>
		<input type="text" name="label" value="{label}"/>
		<input type="submit" value="update" name="action"/>
		<!--
		<input type="submit" value="delete" name="action"/>
		-->
	  </div>
	</form>
  </xsl:template>

  <!-- Identity transformation -->
  <xsl:template match="@*|*">
	<xsl:copy>
	  <xsl:apply-templates select="@*|node()"/>
	</xsl:copy>
  </xsl:template>

  <!-- should probably be in common.xsl -->
  <xsl:template name="insert-msg">
	<xsl:if test="string-length($msg) &gt; 0">
	  <h3 class="msg"><xsl:value-of select="$msg"/></h3>
	</xsl:if>
  </xsl:template>

</xsl:stylesheet>
