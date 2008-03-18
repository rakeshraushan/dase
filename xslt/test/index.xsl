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

  <xsl:template match="/">
	<xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="insert-test-data">
	<div id="test-data">
	  <xsl:choose>
		<xsl:when test="/html/head/dynamic/tests/result/failed/text() = 0">
		  <div class="masthead success"><h1>DASe Tests</h1></div>
		</xsl:when>
		<xsl:otherwise>
		  <div class="masthead failed"><h1>DASe Tests</h1></div>
		</xsl:otherwise>
	  </xsl:choose>
	  <xsl:apply-templates select="/html/head/dynamic/tests"/>
	</div>
  </xsl:template>

  <xsl:template match="insert-base-href">
	<base href="{$app_root}"/>
  </xsl:template>

  <xsl:template match="insert-title">
	<title>DASe Tests</title>
  </xsl:template>

  <xsl:template match="dynamic"/>

  <xsl:template match="tests/test">
	<h5 class="test {@result}"><xsl:value-of select="@name"/></h5>
  </xsl:template>

  <xsl:template match="tests/result">
	<h5><xsl:value-of select="failed/text()"/> failed out of <xsl:value-of select="total/text()"/> run</h5>
  </xsl:template>



  <!-- Identity transformation -->
  <xsl:template match="@*|*">
	<xsl:copy>
	  <xsl:apply-templates select="@*|node()"/>
	</xsl:copy>
  </xsl:template>

</xsl:stylesheet>
