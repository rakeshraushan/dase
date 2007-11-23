<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:a="http://www.w3.org/2005/Atom"
  xmlns:opensearch="http://a9.com/-/spec/opensearch/1.1/"
  xmlns="http://www.w3.org/1999/xhtml"
  >
  <!-- include templateto process each item-->
  <xsl:include href="item2entry.xsl"/>
  <xsl:output method="xml" encoding="UTF-8" indent="yes"/>
  <xsl:preserve-space elements="*"/>
  <!-- use services to get any needed content -->
  <xsl:variable name="source" select="document($src)"/>

  <xsl:template match="/">
	<xsl:apply-templates select="$source/items"/>
  </xsl:template>

  <xsl:template match="items">
	<a:feed >
	  <a:title>DASe Search Result</a:title>
	  <a:subtitle><xsl:value-of select="subtitle/text()"/></a:subtitle>
	  <a:id><xsl:value-of select="concat($app_root,'search/',items/hash/text())"/></a:id>
	  <opensearch:totalResults><xsl:value-of select="total/text()"/></opensearch:totalResults>
	  <opensearch:startIndex><xsl:value-of select="start/text()"/></opensearch:startIndex>
	  <opensearch:itemsPerPage><xsl:value-of select="max/text()"/></opensearch:itemsPerPage>
	  <a:updated><xsl:value-of select="updated/text()"/></a:updated>
	  <a:generator uri="http://daseproject.org" version="1.0">DASe</a:generator>
	  <a:link rel="self" type="application/atom+xml" href="{concat($app_root,'atom/',request/@url)}"/>
	  <a:link rel="alternate" type="application/xhtml+xml" href="{concat($app_root,request/@url)}"/>
	  <a:link rel="http://daseproject.org/relation/search-tallies" type="application/xhtml+xml" href="{concat($app_root,'html/tallies/',request/@url)}"/>
	  <xsl:if test="request-previous">
		<a:link rel="previous" type="application/xhtml+xml" href="{concat($app_root,request-previous/@url)}"/>
	  </xsl:if>
	  <xsl:if test="request-next">
		<a:link rel="next" type="application/xhtml+xml" href="{concat($app_root,request-next/@url)}"/>
	  </xsl:if>
	  <a:author><a:name>DASe</a:name></a:author>
	  <xsl:apply-templates select="item"/>
	</a:feed>
  </xsl:template>


<!-- Identity transformation -->
<xsl:template match="@*|*">
  <xsl:copy>
	<xsl:apply-templates select="@*|node()"/>
  </xsl:copy>
</xsl:template>

</xsl:stylesheet>
