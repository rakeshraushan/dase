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

  <!-- use services to get any needed content -->
  <xsl:variable name="coll" select="document($src)/atom:feed"/>

  <xsl:template match="/">
	<xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="insert-page-hook">
	<xsl:value-of select="$page_hook"/>
  </xsl:template>

  <xsl:template match="insert-base-href">
	<base href="{$app_root}"/>
  </xsl:template>

  <xsl:template match="insert-title">
	<title>Browse Collection</title>
  </xsl:template>

  <xsl:template match="insert-content">
	<div class="full" id="browse">
	  <div id="msg" class="alert hide"></div>
	  <xsl:call-template name="collection-label"/>
	  <h3>Search:</h3>
	  <xsl:call-template name="collection-search-form"/>
	  <div id="browseColumns">
		<h3>Browse:</h3>
		<div id="catColumn">
		  <h4>Select Attribute Group:</h4>
		  <xsl:call-template name="collection-category-links"/>
		</div>
		<div id="attColumn" class="collection/{$coll/atom:category[@scheme='http://daseproject.org/category/collection/ascii_id']/@term}/attributes/public"></div>
		<div id="valColumn" class="hide"></div>
	  </div> <!-- close browseColumns -->
	  <div class="spacer"/>
	</div> <!-- close content -->
  </xsl:template>

  <xsl:template match="dynamic"/>

  <xsl:template match="insert-timer">
	<!--<xsl:value-of select="$timer"/>-->	
	<!--	<xsl:value-of select="php:functionString('Dase_Timer::getElapsed')"/>-->	
  </xsl:template>

  <!-- Identity transformation -->
  <xsl:template match="@*|*">
	<xsl:copy>
	  <xsl:apply-templates select="@*|node()"/>
	</xsl:copy>
  </xsl:template>

  <xsl:template name="collection-label">
	<div id="collectionAsciiId" class="hide"><xsl:value-of select="$coll/atom:category[@scheme='http://daseproject.org/category/collection/ascii_id']/@term"/></div>
	<div class="contentHeader">
	  <h1>
		<xsl:value-of select="$coll/atom:title/text()"/>
		<xsl:text> (</xsl:text><xsl:value-of select="$coll/atom:category[@scheme='http://daseproject.org/category/collection/item_count']/@term"/> items)
	  </h1>
	  <h3>
		<xsl:value-of select="$coll/atom:subtitle/text()"/>
	  </h3>
	</div>
  </xsl:template>

  <xsl:template name="collection-category-links">
	<a href="collection/{$coll/atom:category[@scheme='http://daseproject.org/category/collection/ascii_id']/@term}/attributes/public" class="spill">Collection Attributes</a>
	<a href="collection/{$coll/atom:category[@scheme='http://daseproject.org/category/collection/ascii_id']/@term}/attributes/admin">Admin Attributes</a>
  </xsl:template>

  <xsl:template name="collection-search-form">
	<form method="get" action="collection/{$coll/atom:category[@scheme='http://daseproject.org/category/collection/ascii_id']/@term}/search">
	  <input type="text" name="q" size="30"/>
	  <input type="submit" value="go" class="button"/>
	</form>
  </xsl:template>

</xsl:stylesheet>
