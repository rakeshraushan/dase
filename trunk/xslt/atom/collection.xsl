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
	  <xsl:apply-templates select="$source/collection"/>
	</feed>
  </xsl:template>

  <xsl:template match="collection">
	<title><xsl:value-of select="@collection_name"/></title>
	<id><xsl:value-of select="concat($app_root,@ascii_id)"/></id>
	<updated><xsl:value-of select="@updated"/></updated>
	<generator uri="http://daseproject.org" version="1.0">DASe</generator>
	<link rel="self" type="application/atom+xml" href="{concat($app_root,'atom/',@ascii_id,'/')}"/>
	<link rel="alternate" type="application/xhtml+xml" href="{concat($app_root,'html/',@ascii_id)}"/>
	<author><name>DASe</name></author>
	<xsl:apply-templates select="attributes/attribute"/>
	<!--
	item types should be represented in anotehr feed
	<xsl:apply-templates select="item-types">
	</xsl:apply-templates>
	-->
  </xsl:template>

  <xsl:template match="attribute">
	<entry>
	  <title><xsl:value-of select="@attribute_name"/></title>
	  <id><xsl:value-of select="concat($app_root,../../@ascii_id,'/',@ascii_id)"/></id>
	  <category term="attribute" scheme="{concat($app_root,'categories')}" label="Attribute"/>
	  <updated><xsl:value-of select="@updated"/></updated>
	  <link type="application/atom+xml" href="{concat($app_root,'atom/',../../@ascii_id,'/att/',@ascii_id,'/')}"/>
	  <link type="application/xhtml+xml" href="{concat($app_root,'html/',../../@ascii_id,'/att/',@ascii_id)}"/>
	  <xsl:if test="@in_basic_search = 1">
		<category term="in_basic_search" scheme="{concat($app_root,'categories/attribute/basic_search')}" label="in_basic_search"/>
	  </xsl:if>
	  <xsl:if test="@in_basic_search = 1">
		<category term="in_basic_search" scheme="{concat($app_root,'categories/attribute/public_private')}" label="is_public"/>
	  </xsl:if>
	  <xsl:if test="@is_on_list_display = 1">
		<category term="is_on_list_display" scheme="{concat($app_root,'categories/attribute/list_display')}" label="is_on_list_display"/>
	  </xsl:if>
	</entry>
  </xsl:template>


  <!-- Identity transformation -->
  <xsl:template match="@*|*">
	<xsl:copy>
	  <xsl:apply-templates select="@*|node()"/>
	</xsl:copy>
  </xsl:template>

</xsl:stylesheet>
