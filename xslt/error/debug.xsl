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

  <xsl:template match="insert-error-data">
	<div id="error-data">
	  <div class="masthead"><h1>DASe Error</h1></div>
	  <h2>controller</h2>
	  <xsl:call-template name="dase"/>
	  <h2>routes</h2>
	  <xsl:call-template name="routes"/>
	</div>
  </xsl:template>

  <xsl:template match="insert-base-href">
	<base href="{$app_root}"/>
  </xsl:template>

  <xsl:template match="insert-title">
	<title>DASe::error</title>
  </xsl:template>

  <xsl:template match="dynamic"/>

  <xsl:template name="dase">
	<xsl:apply-templates select="/html/head/dynamic/errors/dase"/>
  </xsl:template>

  <xsl:template name="routes">
	<xsl:apply-templates select="/html/head/dynamic/errors/routes"/>
  </xsl:template>

  <xsl:template match="dase">
	<table>
	  <xsl:apply-templates mode="dase"/>
	</table>
  </xsl:template>

  <xsl:template match="routes">
	<table class="routes">
	  <xsl:apply-templates select="route" mode="routes"/>
	</table>
  </xsl:template>

  <xsl:template match="route/regex" mode="routes">
	<tr>
	  <th><xsl:value-of select="text()"/></th>
	  <td><xsl:apply-templates select=".." mode="routes-sub"/></td>
	</tr>
  </xsl:template>

  <xsl:template match="route/*" mode="routes"/>
  <xsl:template match="regex" mode="routes-sub"/>

  <xsl:template match="action|handler|method|auth|name|params|caps" mode="routes-sub">
	<p><xsl:value-of select="local-name()"/> : <xsl:value-of select="text()"/></p>
  </xsl:template>

  <xsl:template match="http_error_code|action|handler|method|query_string|request_url|response_mime_type" mode="dase">
	<tr>
	  <th><xsl:value-of select="local-name()"/></th>
	  <td><xsl:value-of select="text()"/></td>
	</tr>
  </xsl:template>

  <!-- Identity transformation -->
  <xsl:template match="@*|*">
	<xsl:copy>
	  <xsl:apply-templates select="@*|node()"/>
	</xsl:copy>
  </xsl:template>

</xsl:stylesheet>
