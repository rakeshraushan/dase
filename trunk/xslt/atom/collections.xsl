<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns="http://www.w3.org/2005/Atom"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  >
  <xsl:output method="xml" encoding="UTF-8" indent="yes"/>
  <xsl:preserve-space elements="*"/>
  <!-- use services to get any needed content -->
  <xsl:variable name="source" select="document($src)"/>

  <xsl:template match="/">
	<xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="feed">
	<feed>
	  <xsl:apply-templates select="$source/collections"/>
	</feed>
  </xsl:template>

  <xsl:template match="collections">
	<title>DASe Collections</title>
	<id><xsl:value-of select="$app_root"/></id>
	<updated><xsl:value-of select="@updated"/></updated>
	<generator uri="http://daseproject.org" version="1.0">DASe</generator>
	<link rel="self" type="application/atom+xml" href="{concat($app_root,'atom/')}"/>
	<link rel="alternate" type="application/xhtml+xml" href="{concat($app_root,'html/')}"/>
	<author><name>DASe</name></author>
	<xsl:apply-templates select="collection"/>
  </xsl:template>

  <xsl:template match="collection">
	<entry><title type="html"><xsl:value-of select="@collection_name"/></title>
	  <id><xsl:value-of select="concat($app_root,@ascii_id,'/')"/></id>
	  <category term="public" scheme="{concat($app_root,'public')}" label="Public"/>
	  <updated><xsl:value-of select="@updated"/></updated>
	  <link type="application/atom+xml" href="{concat($app_root,'atom/',@ascii_id,'/')}"/>
	  <link type="application/xhtml+xml" href="{@url}"/>
	</entry>
  </xsl:template>

  <!-- Identity transformation -->
  <xsl:template match="@*|*">
	<xsl:copy>
	  <xsl:apply-templates select="@*|node()"/>
	</xsl:copy>
  </xsl:template>

</xsl:stylesheet>
