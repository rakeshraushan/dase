<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="xml" 
	indent="yes"
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" 
	encoding="UTF-8"/>
  <!-- use services to get any needed content -->
  <xsl:variable name="source" select="document($src)"/>

  <xsl:preserve-space elements="*"/>

  <xsl:template match="/">
	<xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="insert-base-href">
	<base href="{$app_root}"/>
  </xsl:template>

  <xsl:template match="insert-data">
	<ul>
	  <xsl:text>
	  </xsl:text>
	  <xsl:apply-templates select="$source/items/tallies"/>
	  <xsl:text>
	  </xsl:text>
	</ul>
  </xsl:template>

  <xsl:template match="tallies">
	<!--
	<li>
	  <a href="{concat($app_root,../request/text())}"><xsl:text>All Collections</xsl:text></a>
	  <xsl:text>: </xsl:text>
	  <xsl:value-of select="../total/text()"/>
	</li>
	-->
	<xsl:apply-templates select="tally"/>
  </xsl:template>

  <xsl:template match="tally">
	<xsl:text>
	</xsl:text>
	<li>
	  <!-- note that we ASSUME the newly added coll_ascii_id url param won't be the first, so we use & not ?-->
	  <a href="{concat($app_root,../../request/@url,'&amp;collection_ascii_id=',@collection_ascii_id)}"><xsl:value-of select="@collection_name"/></a>
	  <xsl:text>: </xsl:text>
	  <xsl:value-of select="@total"/>
	  [<a href="{concat($app_root,../../request/@url,'&amp;nc=',@collection_ascii_id)}" class="delete" title="omit from search">X</a>]
	</li>
  </xsl:template>

  <!-- Identity transformation -->
  <xsl:template match="@*|*">
	<xsl:copy>
	  <xsl:apply-templates select="@*|node()"/>
	</xsl:copy>
  </xsl:template>

</xsl:stylesheet>
