<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns:atom="http://www.w3.org/2005/Atom"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  >
  <xsl:output method="xml" 
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" 
	encoding="UTF-8"/>
  <!-- include general stylesheet -->
  <xsl:include href="../site/stylesheet.xsl"/>
  <!-- use services to get any needed content -->
  <xsl:variable name="collections-list" select="document($collections)/atom:feed"/>

  <xsl:template match="insert-collection-list-items">
	<xsl:apply-templates select="$collections-list/atom:entry"/>
  </xsl:template>

  <xsl:template name="collection">
	<li id="{atom:content}">
	  <input name="c" value="{atom:content}" checked="checked" type="checkbox"/>
	  <xsl:text> </xsl:text>
	  <a href="{atom:content}" class="checkedCollection"><xsl:value-of select="atom:title"/></a>
	  <xsl:text> </xsl:text>
	  <span class="tally"></span>
	</li>
  </xsl:template>

  <xsl:template match="atom:entry">
	<xsl:call-template name="collection"/>
  </xsl:template>

</xsl:stylesheet>
