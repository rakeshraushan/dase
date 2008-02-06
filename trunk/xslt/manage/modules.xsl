<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:h="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="h"
  >
  <xsl:import href="common.xsl"/>

  <xsl:output method="xml" 
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" 
	encoding="UTF-8"/>

  <xsl:template match="insert-content">
	<ul>
	  <xsl:call-template name="module_names"/>
	</ul>
  </xsl:template>

  <xsl:template name="module_names">
	<xsl:apply-templates select="/html/head/dynamic/modules/module">
	  <xsl:sort/>
	</xsl:apply-templates>
  </xsl:template>

  <xsl:template match="module">
	<li><xsl:value-of select="text()"/></li>
  </xsl:template>

</xsl:stylesheet>
