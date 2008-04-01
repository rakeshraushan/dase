<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns:atom="http://www.w3.org/2005/Atom"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:h="http://www.w3.org/1999/xhtml"
  xmlns:d="http://daseproject.org"
  xmlns:php="http://php.net/xsl"
  xsl:extension-element-prefixes="php"
  exclude-result-prefixes="atom h d php"
  >
  <xsl:import href="common.xsl"/> 
  <xsl:output method="xml" indent="yes"
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" 
	encoding="UTF-8"/>

  <xsl:variable name="tag_type" select="translate($items/atom:category[@scheme='http://daseproject.org/category/tag_type']/@term,'ABCDEFGHIJKLMNOPQRSTUVWXYZ_','abcdefghijklmnopqrstuvwxyz_')"/>
  <xsl:variable name="tag_ascii_id" select="$items/atom:category[@scheme='http://daseproject.org/category/tag']/@term"/>
  <xsl:variable name="tag_name" select="$items/atom:category[@scheme='http://daseproject.org/category/tag']/@label"/>
  <xsl:variable name="user" select="html/head/dynamic/user"/>

  <xsl:template match="insert-content">
	<!-- stash the tag_type for javascript to have access -->
	<div class="data" id="tag_type"><xsl:value-of select="$tag_type"/></div>
	<div class="data" id="tag_name"><xsl:value-of select="$tag_name"/></div>
	<!--uses the tag_type category to get page type -->
	<div class="full" id="{$tag_type}">
	  <xsl:call-template name="insert-msg"/>
	  <div id="msg" class="alert hide"></div>
	  <div id="contentHeader">
		<h2><xsl:value-of select="$items/atom:title"/></h2>
		<h3><xsl:value-of select="$items/atom:subtitle"/></h3>
	  </div>
	  <form method="post" action="user/{$user/eid}/tag/{$tag_ascii_id}/remove_items">	
		<table id="itemSet">
		  <xsl:apply-templates select="$items/atom:entry" mode="items"/>
		</table>
		<a href="" id="checkall">check/uncheck all</a>
		<input type="submit" name="remove_checked" id="removeFromSet" value="remove checked items from set"/>
	  </form>
	  <form id="saveToForm" method="post" action="save">	
		<div id="saveChecked"></div>
	  </form>
	  <div class="spacer"/>
	</div>
  </xsl:template>

</xsl:stylesheet>
