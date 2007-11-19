<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="xml" 
	indent="yes"
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" 
	encoding="UTF-8"/>
  <!-- use services to get any needed content -->
  <xsl:variable name="source" select="document($src)"/>

  <xsl:preserve-space elements="*"/>

  <xsl:template match="/">
	<xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="insert-base-href">
	<base href="{$app_root}"/>
  </xsl:template>

  <xsl:template match="insert-data">
	<xsl:text>
	</xsl:text>
	<ul id="xoxo">
	  <xsl:apply-templates select="$source" mode="source"/>
	</ul>
  </xsl:template>

  <xsl:template match="*" mode="source">
	<xsl:text>
	</xsl:text>
	<li class="{local-name()}">
	  <xsl:if test="not(text())">
		<a><xsl:value-of select="local-name()"/></a>
	  </xsl:if>
	  <xsl:if test="text()">
		<a><xsl:value-of select="text()"/></a>
		<dl class="hide">
		  <xsl:apply-templates select="@*" mode="source"/>
		</dl>
	  </xsl:if>
	  <xsl:if test="child::node()">
		<ul>
		  <xsl:apply-templates select="child::*" mode="source"/>
		</ul>
	  </xsl:if>
	  <xsl:text>
	  </xsl:text>
	</li>
  </xsl:template>

  <xsl:template match="@*" mode="source">
	<xsl:text>
	</xsl:text>
	<dt><xsl:value-of select="name()"/></dt>
	<xsl:choose>
	  <xsl:when test="name()='url'">
		<dd><a class="metadata" href="{.}"><xsl:value-of select="."/></a></dd>
	  </xsl:when>
	  <xsl:when test="1">
		<dd><xsl:value-of select="."/></dd>
	  </xsl:when>
	</xsl:choose>
  </xsl:template>

  <!-- Identity transformation -->
  <xsl:template match="@*|*">
	<xsl:copy>
	  <xsl:apply-templates select="@*|node()"/>
	</xsl:copy>
  </xsl:template>

</xsl:stylesheet>
