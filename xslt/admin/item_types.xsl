<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:h="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="h"
  >
  <xsl:import href="common.xsl"/>

  <xsl:output method="xml" 
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" 
	encoding="UTF-8"/>

  <xsl:template match="insert-content">
	<h1 id="pageHeader">Item Types for <xsl:value-of select="$collection/collection_name/text()"/></h1>
	<!--
	<h2><xsl:value-of select="$user/ppd"/></h2>
	-->
	<div id="collectionData">
	  <table id="dataDisplay">
		<tr>
		  <th>Name</th>
		  <th>Ascii Id</th>
		  <th>Description</th>
		  <th>Attributes</th>
		</tr>
		<xsl:apply-templates select="$collection/item_type" mode="coll"/>
	  </table>
	</div>
  </xsl:template>


  <xsl:template match="item_type" mode="coll">
	<tr>
	  <th class="rows">
		<xsl:value-of select="name"/>
	  </th>
	  <td class="data">
		<xsl:value-of select="ascii_id"/>
	  </td>
	  <td>
		<xsl:value-of select="description"/>
	  </td>
	  <td class="data">
		<ul>
		  <xsl:apply-templates select="attribute" mode="type"/>
		</ul>
	  </td>
	</tr>
  </xsl:template>

  <xsl:template match="attribute" mode="type">
	<li><xsl:value-of select="@name"/> (<xsl:value-of select="@cardinality"/>)</li>
  </xsl:template>

  <!--
  <xsl:template match="script[@type='text/javascript'][position() = last()]">
	<xsl:copy-of select="."/>
	<xsl:text>
	</xsl:text>
	<script type="text/javascript" src="scripts/collection.js"></script>
  </xsl:template>
  -->

</xsl:stylesheet>
