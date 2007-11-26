<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/2005/Atom"
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
	<feed >
	  <xsl:apply-templates select="$source/item" mode="item_feed"/>
	</feed>
  </xsl:template>

  <xsl:template match="item" mode="item_feed">
	<title><xsl:value-of select="text()"/></title>
	<subtitle><xsl:value-of select="subtitle/text()"/></subtitle>
	<id><xsl:value-of select="concat($app_root,@collection_ascii_id,'/',@serial_number)"/></id>
	<updated><xsl:value-of select="@last_update"/></updated>
	<generator uri="http://daseproject.org" version="1.0">DASe</generator>
	<link rel="self" type="application/atom+xml" href="{concat($app_root,'atom/',request/@url)}"/>
	<link rel="alternate" type="application/xhtml+xml" href="{concat($app_root,'html/',@collection_ascii_id,'/',@serial_number)}"/>
	<xsl:if test="request-previous">
	  <link rel="previous" type="application/xhtml+xml" href="{concat($app_root,request-previous/@url)}"/>
	</xsl:if>
	<xsl:if test="request-next">
	  <link rel="next" type="application/xhtml+xml" href="{concat($app_root,request-next/@url)}"/>
	</xsl:if>
	<author><name>DASe</name></author>
	<xsl:apply-templates select="../item"/>
  </xsl:template>

  <!-- Identity transformation -->
  <xsl:template match="@*|*">
	<xsl:copy>
	  <xsl:apply-templates select="@*|node()"/>
	</xsl:copy>
  </xsl:template>

</xsl:stylesheet>
