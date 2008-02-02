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
  <xsl:variable name="collections" select="document($src)"/>

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
	<title>List of All Collection</title>
  </xsl:template>

  <xsl:template match="insert-content">
	<div class="list browse">
	  <a id="maincontent" name="maincontent"></a>
	  <div id="msg" class="alert hide"></div>
	  <div class="searchBoxLabel">Search selected collection(s):</div> 
	  <form id="searchCollections" method="get" action="search">
		<div>
		  <input type="text" name="q" size="30"/>
		  <input type="submit" value="Search" class="button"/>
		</div>
		<ul id="collectionList" class="pageList multicheck">
		  <xsl:apply-templates select="$collections/atom:feed/atom:entry"/>
		  <li id="specialAccessLabel" class="label hide">Special Access Collections</li>
		</ul>
	  </form>

	  <h3 class="browsePublicTags"><a href="action/list_public_tags/">Browse Public User Collections/Slideshows</a></h3>
	</div>
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

  <xsl:template match="div[@id='msg']">
	<div class="alert">
	  <xsl:value-of select="$msg"/>
	</div>
  </xsl:template>

  <xsl:template match="atom:entry">
	  <xsl:text>
	  </xsl:text>
	  <li id="{atom:content/text()}">
		<input name="c" value="{atom:content/text()}" checked="checked" type="checkbox"/>
	  <xsl:text> </xsl:text>
	  <a href="collection/{atom:content/text()}" class="checkedCollection"><xsl:value-of select="atom:title"/></a>
	  <xsl:text> </xsl:text>
	  <span class="tally"></span>
	</li>
  </xsl:template>

</xsl:stylesheet>
