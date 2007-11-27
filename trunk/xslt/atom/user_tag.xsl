<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/2005/Atom"
  xmlns:opensearch="http://a9.com/-/spec/opensearch/1.1/"
  >
  <!-- include templateto process each item-->
  <xsl:include href="item2entry.xsl"/>
  <xsl:output method="xml" encoding="UTF-8" indent="yes"/>
  <xsl:preserve-space elements="*"/>
  <!-- use services to get any needed content -->
  <xsl:variable name="source" select="document($src)"/>

  <xsl:template match="/">
	<xsl:apply-templates select="$source/tag"/>
  </xsl:template>

  <xsl:template match="tag">
	<feed >
	  <title><xsl:value-of select="name/text()"/></title>
	  <!--
	  <subtitle><xsl:value-of select="subtitle/text()"/></subtitle>
	  <id><xsl:value-of select="concat($app_root,'search/',items/hash/text())"/></id>
	  <updated><xsl:value-of select="updated/text()"/></updated>
	  <generator uri="http://daseproject.org" version="1.0">DASe</generator>
	  <link rel="self" type="application/atom+xml" href="{concat($app_root,'atom/',request/@url)}"/>
	  <link rel="alternate" type="application/xhtml+xml" href="{concat($app_root,request/@url)}"/>
	  <link rel="http://daseproject.org/relation/search-tallies" type="application/xhtml+xml" href="{concat($app_root,'html/tallies/',request/@url)}"/>
	  <xsl:if test="request-previous">
		<link rel="previous" type="application/xhtml+xml" href="{concat($app_root,request-previous/@url)}"/>
	  </xsl:if>
	  <xsl:if test="request-next">
		<link rel="next" type="application/xhtml+xml" href="{concat($app_root,request-next/@url)}"/>
	  </xsl:if>
	  <author><name>DASe</name></author>
	  -->
	  <xsl:apply-templates select="item"/>
	</feed>
  </xsl:template>


<!-- Identity transformation -->
<xsl:template match="@*|*">
  <xsl:copy>
	<xsl:apply-templates select="@*|node()"/>
  </xsl:copy>
</xsl:template>

</xsl:stylesheet>
