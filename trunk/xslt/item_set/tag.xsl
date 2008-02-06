<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns:atom="http://www.w3.org/2005/Atom"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:h="http://www.w3.org/1999/xhtml"
  xmlns:d="http://daseproject.org"
  xmlns:php="http://php.net/xsl"
  xsl:extension-element-prefixes="php"
  exclude-result-prefixes="atom h d"
  >
  <xsl:output method="xml" indent="yes"
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" 
	encoding="UTF-8"/>

  <!-- use services to get any needed content -->
  <xsl:variable name="items" select="document($src)/atom:feed"/>

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
	<title>Item Set</title>
  </xsl:template>

  <xsl:template match="ul[@id='menu']/li[position() = last()]">
	<xsl:copy-of select="."/>
	<li class="marked" id="markedList"><a href="" class="main">Marked Items</a>
	  <ul class="hide" id="marked">
		<li class="placeholder"></li>
		<!-- ajax fills in here -->
	  </ul>
	</li>
  </xsl:template>

  <xsl:template match="insert-content">
	<div class="full browse">
	  <a id="maincontent" name="maincontent"></a>
	  <div id="msg" class="alert hide"></div>
	  <div id="searchResults">
		<form method="post" action="xxxx">	
		  <h1><xsl:value-of select="$items/atom:subtitle/text()"/></h1>
		  <table>
			<xsl:apply-templates select="$items/atom:entry" mode="items"/>
		  </table>
		</form>
	  </div>
	  <div class="spacer"/>
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

  <!-- note that column numbers are hard-coded in 2 places below
  where they cannot be included in a predicate in a match-->
  <xsl:variable name="columns" select="5"/>

  <xsl:template match="collection">
	<a href="{$app_root}{@ascii_id}"><xsl:value-of select="@name"/></a> (<xsl:value-of select="$total"/> items)
  </xsl:template>

  <!-- from http://www.jguru.com/faq/view.jsp?EID=1094766 (xslt html tables)-->
  <!-- note per Kay p.441 predicates in match cannot include variables ugh -->
  <xsl:template match="atom:entry[(position()-1) mod 5 != 0]" mode="items">
	<!-- /dev/null -->
  </xsl:template>

  <xsl:template match="atom:entry[(position()-1) mod 5 = 0]" mode="items">
	<xsl:text>
	</xsl:text>
	<tr>
	  <xsl:text>
	  </xsl:text>
	  <td>
		<div class="checkNum">
		  <input type="checkbox" name="item_id" value="{atom:category[@scheme='http://daseproject.org/category/item/id']/@term}"/>
		  <xsl:value-of select="atom:category[@scheme='http://daseproject.org/category/item_set/index']/@label"/><xsl:text>.</xsl:text>
		</div>
		<div class="cartAdd">
		  <span class="hide">in cart</span> <a href="#" class="hide" id="addToCart_{atom:category[@scheme='http://daseproject.org/category/item/id']/@term}">add to cart</a>
		</div>
		<div class="image">
		  <a href="{atom:link[@rel='http://daseproject.org/relation/search-item']/@href}">
			<xsl:copy-of select="atom:content/h:div/h:img[@class='thumbnail']"/>
		  </a>
		</div>
		<div class="spacer"></div>
		<div class="caption">
		  <h4>
			<xsl:value-of select="substring(atom:title,0,80)"/>
			<xsl:if test="string-length(atom:title) &gt; 80">...</xsl:if>
		  </h4>
		  <h4 class="collection_name"><xsl:value-of select="atom:category[@scheme='http://daseproject.org/category/collection']/@label"/></h4>
		</div>

	  </td>
	  <xsl:for-each select="following-sibling::atom:entry[position() &lt; $columns]">
		<xsl:text>
		</xsl:text>
		<td>
		  <div class="checkNum">
			<input type="checkbox" name="item_id" value="{atom:category[@scheme='http://daseproject.org/category/item/id']/@term}"/>
			<xsl:value-of select="atom:category[@scheme='http://daseproject.org/category/item_set/index']/@label"/><xsl:text>.</xsl:text>
		  </div>
		  <div class="cartAdd">
			<span class="hide">in cart</span> <a href="#" class="hide" id="addToCart_{atom:category[@scheme='http://daseproject.org/category/item/id']/@term}">add to cart</a>
		  </div>
		  <div class="image">
			<a href="{atom:link[@rel='http://daseproject.org/relation/search-item']/@href}">
			  <xsl:copy-of select="atom:content/h:div/h:img[@class='thumbnail']"/>
			</a>
		  </div>
		  <div class="spacer"></div>
		  <div class="caption">
			<h4>
			  <xsl:value-of select="substring(atom:title,0,80)"/>
			  <xsl:if test="string-length(atom:title) &gt; 80">...</xsl:if>
			</h4>
			<h4 class="collection_name"><xsl:value-of select="atom:category[@scheme='http://daseproject.org/category/collection']/@label"/></h4>
		  </div>
		</td>
		<!-- this will fill out blank cells in table-->
		<xsl:if test="position() = last() and last() + 1 != $columns">
		  <td colspan="0" class="blank">
			<xsl:text> </xsl:text>
		  </td>
		</xsl:if>
	  </xsl:for-each>
	  <!-- this will fill out blank cells in table-->
	  <xsl:if test="position() = last()">
		<td colspan="0" class="blank">
		  <xsl:text>  </xsl:text>
		</td>
	  </xsl:if>
	</tr>
  </xsl:template>
</xsl:stylesheet>
