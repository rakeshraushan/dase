<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns:atom="http://www.w3.org/2005/Atom"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:media="http://search.yahoo.com/mrss/"
  xmlns:h="http://www.w3.org/1999/xhtml"
  xmlns:d="http://daseproject.org/ns/1.0"
  xmlns:php="http://php.net/xsl"
  xsl:extension-element-prefixes="php"
  exclude-result-prefixes="atom h d php media"
  >
  <xsl:output method="xml" indent="yes"
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" 
	encoding="UTF-8"/>

  <!-- use services to get any needed content -->
  <xsl:variable name="it" select="document($src)/atom:feed"/>

  <xsl:variable name="coll" select="$it/atom:entry/atom:category[@scheme='http://daseproject.org/category/collection']/@term"/>
  <xsl:variable name="coll_name" select="$it/atom:entry/atom:category[@scheme='http://daseproject.org/category/collection']/@label"/>

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
	<title>View Item</title>
  </xsl:template>

  <xsl:template match="insert-content">
	<!-- tag_type determines 'id' and thus "look" of page -->
	<div class="full" id="{translate($it/atom:category[@scheme='http://daseproject.org/category/tag_type']/@term,'ABCDEFGHIJKLMNOPQRSTUVWXYZ_','abcdefghijklmnopqrstuvwxyz_')}">
	  <div id="collectionAsciiId" class="data"><xsl:value-of select="$coll"/></div>
	  <div id="contentHeader">
		<h1><a href="collection/{$coll}"><xsl:value-of select="$coll_name"/></a></h1>
		<h2><xsl:value-of select="$it/atom:title"/></h2>
		<h3><xsl:value-of select="$it/atom:subtitle"/></h3>
		<h4>
		  <a href="{$it/atom:link[@rel='previous']/@href}">prev</a> |
		  <a href="{$it/atom:link[@rel='http://daseproject.org/relation/feed-link']/@href}">up</a> |
		  <a href="{$it/atom:link[@rel='next']/@href}">next</a> 
		</h4>
	  </div> <!-- close contentHeader -->
	  <table id="item">
		<tr>
		  <td class="image">
			<img src="{$it/atom:entry/atom:content/h:div/h:img[@class='viewitem']/@src}"/>
			<h4>Media:</h4>
			<ul>
			  <xsl:apply-templates select="$it/atom:entry/media:content/media:thumbnail" mode="media"/>
			  <xsl:apply-templates select="$it/atom:entry/media:content" mode="media"/>
			</ul>
		  </td>
		  <td class="metadata">
			<h3><a href="collection/{$coll}"><xsl:value-of select="$coll_name"/></a></h3>
			<dl id="metadata" class="{$coll}">
			  <xsl:apply-templates select="$it/atom:entry/d:*" mode="keyvals"/>
			</dl>
			<!--
			<a href="view_admin_metadata" class="toggle" id="toggle_adminMetadata">show/hide admin metadata</a>
			<dl id="adminMetadata" class="hide">
			  <xsl:apply-templates select="$it/atom:entry/atom:content/h:div/h:dl[@class='admin_metadata']" mode="keyvals"/>
			</dl>
			-->
			<ul id="itemLinks">
			  <a href="atom/collection/{$coll}/{$it/atom:entry/d:admin_serial_number}">atom</a>
			</ul>
		  </td>
		</tr>
	  </table>
	  <xsl:apply-templates select="$it/atom:entry/atom:link[@rel='edit']" mode="edit"/>
	</div> <!-- close content -->
  </xsl:template>

  <xsl:template match="atom:link[@rel='edit']" mode="edit">
	<div><a class="hide" id="editLink" href="{@href}">edit item</a></div>
  </xsl:template>

  <xsl:template match="d:*" mode="keyvals">
	<dt><xsl:value-of select="@d:label"/></dt>
	<dd><a href="search?{$coll}.{local-name()}={@d:encoded}"><xsl:value-of select="text()"/></a></dd>
  </xsl:template>

  <!-- todo: fix this...js hook that no longer works-->
  <xsl:template match="h:dl/h:dd[@class='nolink']" mode="keyvals">
	<!-- sets 'item_id' and 'serial_number' as id, so js can grab values -->
	<dd id="{preceding-sibling::h:dt[position()=1]/@class}"><xsl:value-of select="text()"/></dd>
	<dd><xsl:value-of select="text()"/></dd>
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

  <xsl:template match="media:thumbnail|media:content" mode="media">
	<li><a href="{@url}"><xsl:value-of select="@width"/>x<xsl:value-of select="@height"/></a></li>
  </xsl:template>

  <xsl:template match="h:dl|h:ul">
	<xsl:copy>
	  <xsl:apply-templates/>
	</xsl:copy>
  </xsl:template>

</xsl:stylesheet>
