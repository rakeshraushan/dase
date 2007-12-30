<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns:h="http://www.w3.org/1999/xhtml"
  xmlns:atom="http://www.w3.org/2005/Atom"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  >
  <xsl:output method="xml" indent="yes" encoding="UTF-8"/>
  <xsl:preserve-space elements="*"/>
  <!-- include general stylesheet -->
  <xsl:include href="../site/stylesheet.xsl"/>
  <!-- use services to get any needed content -->
  <xsl:variable name="collections-list" select="document($src)/atom:feed"/>

  <!-- proof of concept: how to add javascript to the page
  <xsl:template match="script[@type='text/javascript'][position() = 1]">
	<xsl:copy-of select="."/>
	<xsl:text>
	</xsl:text>
	<script type="text/javascript" src="scripts/test.js"></script>
  </xsl:template>
  -->

  <xsl:template match="div[@id='msg']">
	<div class="alert">
	  <xsl:value-of select="$msg"/>
	</div>
  </xsl:template>

  <xsl:template match="insert-collection-list-items">
	<xsl:apply-templates select="$collections-list"/>
  </xsl:template>

  <xsl:template match="atom:feed">
	<xsl:apply-templates select="atom:entry"/>
  </xsl:template>

  <xsl:template match="atom:entry">
	  <xsl:text>
	  </xsl:text>
	  <li id="{atom:content/text()}">
		<input name="c" value="{atom:content/text()}" checked="checked" type="checkbox"/>
	  <xsl:text> </xsl:text>
	  <a href="{atom:content/text()}" class="checkedCollection"><xsl:value-of select="atom:title"/></a>
	  <xsl:text> </xsl:text>
	  <span class="tally"></span>
	</li>
  </xsl:template>

</xsl:stylesheet>
