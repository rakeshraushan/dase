<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:a="http://www.w3.org/2005/Atom"
  xmlns="http://www.w3.org/1999/xhtml"
  >
  <xsl:include href="item2entry.xsl"/>
  <xsl:output method="xml" encoding="UTF-8" indent="yes"/>
  <xsl:preserve-space elements="*"/>
  <!-- use services to get any needed content -->
  <xsl:variable name="source" select="document($src)"/>

  <xsl:template match="/">
	<xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="feed">
	<a:feed >
	  <xsl:apply-templates select="$source/item" mode="item_feed"/>
	</a:feed>
  </xsl:template>

  <xsl:template match="item" mode="item_feed">
	<a:title><xsl:value-of select="text()"/></a:title>
	<a:subtitle><xsl:value-of select="subtitle/text()"/></a:subtitle>
	<a:id><xsl:value-of select="concat($app_root,@collection_ascii_id,'/',@serial_number)"/></a:id>
	<a:updated><xsl:value-of select="@last_update"/></a:updated>
	<a:generator uri="http://daseproject.org" version="1.0">DASe</a:generator>
	<a:link rel="self" type="application/atom+xml" href="{concat($app_root,'atom/',@collection_ascii_id,'/',@serial_number)}"/>
	<a:link rel="alternate" type="application/xhtml+xml" href="{concat($app_root,'html/',@collection_ascii_id,'/',@serial_number)}"/>
	<xsl:if test="request-previous">
	  <a:link rel="previous" type="application/xhtml+xml" href="{concat($app_root,request-previous/@url)}"/>
	</xsl:if>
	<xsl:if test="request-next">
	  <a:link rel="next" type="application/xhtml+xml" href="{concat($app_root,request-next/@url)}"/>
	</xsl:if>
	<a:author><a:name>DASe</a:name></a:author>
	<xsl:apply-templates select="../item"/>
  </xsl:template>

  <!-- Identity transformation -->
  <xsl:template match="@*|*">
	<xsl:copy>
	  <xsl:apply-templates select="@*|node()"/>
	</xsl:copy>
  </xsl:template>

</xsl:stylesheet>
