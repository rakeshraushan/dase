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

  <!--
  <xsl:template match="ul[@id='menu']/li[position() = last()]">
	<xsl:copy-of select="."/>
	<li class="searches" id="searchesList"><a href="" class="main">My Searches</a>
	  <ul class="hide" id="searches">
		<li><a href="">aaa</a></li>
	  </ul>
	</li>
  </xsl:template>
  -->

  <xsl:template match="insert-content">
	<div class="full" id="browse">
	  <div id="msg" class="alert hide"></div>
	  <!-- SEARCH FORM -->
	  <form id="searchCollectionsAdv" method="get" action="search">
		<div>
		  <input id="queryInput" type="text" name="q" size="30"/>
		  <input type="submit" value="Search" class="button"/>
		  <select id="collectionsSelect" name="collection_ascii_id">
		  </select>
		  <span id="preposition" class="hide">in</span>
		  <select id="attributesSelect" class="hide">
		  </select>
		  <input id="refineCheckbox" type="checkbox"/>refine current result
		</div>
		<div id="refinements"/>
	  </form>
	  <div id="searchResults">
		<xsl:copy-of select="$items/atom:subtitle/h:div/h:h2[@class='searchEcho']"/>
		<form method="post" action="xxxx">	
		  <h1><xsl:value-of select="$items/atom:subtitle/text()"/></h1>
		  <div class="pageControls">
			<a href="{$items/atom:link[@rel='previous']/@href}">prev</a> |
			<a href="{$items/atom:link[@rel='next']/@href}">next</a> 
		  </div>
		  <table id="itemSet">
			<xsl:apply-templates select="$items/atom:entry" mode="items"/>
		  </table>
		  <!-- we just need a place to stash the current url so our refine code can parse it -->
		  <div id="self_url" class="hide"><xsl:value-of select="translate($items/atom:link[@rel='self']/@href,'+',' ')"/></div>
		  <div id="checkItems"></div>
		  <div id="saveToSelector"></div>
		</form>
	  </div>
	</div>
  </xsl:template>

  <xsl:template match="insert-subcontent">
	<div id="searchTallies">
	  <h3>Search Results by Collection</h3>
	  <!--the link to tallies is in the atom document-->
	  <xsl:copy-of select="$items/atom:subtitle/h:div/h:ul"/>
	</div>
	<div id="saveMarkedToCollection">
	</div>
	<div class="spacer"/>
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
		  <xsl:text> </xsl:text>
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
			<xsl:text> </xsl:text>
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
