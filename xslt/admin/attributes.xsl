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
	<h1>Attributes for <xsl:value-of select="$collection/collection_name/text()"/></h1>
	<!--
	<h2><xsl:value-of select="$user/ppd"/></h2>
	-->
	<div id="collectionData">
	  <table id="dataDisplay">
		<tr>
		  <th>Name</th>
		  <th>Ascii Id</th>
		  <th>Updated</th>
		  <th>Usage Notes</th>
		  <th>Input Type</th>
		  <th>Sort Order</th>
		  <th>In Basic Search</th>
		  <th>On List Display</th>
		  <th>Is Public</th>
		</tr>
		<xsl:apply-templates select="$collection/attribute" mode="coll"/>
	  </table>
	</div>
  </xsl:template>


  <xsl:template match="attribute" mode="coll">
	<tr>
	  <th class="rows">
		<xsl:value-of select="attribute_name"/>
	  </th>
	  <td class="data">
		<xsl:value-of select="ascii_id"/>
	  </td>
	  <td>
		<xsl:value-of select="updated"/>
	  </td>
	  <td class="data">
		<xsl:value-of select="usage_notes"/>
	  </td>
	  <td>
		<xsl:value-of select="html_input_type_id"/>
	  </td>
	  <td>
		<input type="text" size="{string-length(sort_order)}" value="{sort_order}"/>
	  </td>
	  <td>
		<xsl:choose>
		  <xsl:when test="in_basic_search = 1">
			<input type="checkbox" name="in_basic_search_{ascii_id}" checked="checked"/>
		  </xsl:when>
		  <xsl:otherwise>
			<input type="checkbox" name="in_basic_search_{ascii_id}"/>
		  </xsl:otherwise>
		</xsl:choose>
	  </td>
	  <td>
		<xsl:choose>
		  <xsl:when test="is_on_list_display = 1">
			<input type="checkbox" name="is_on_list_display_{ascii_id}" checked="checked"/>
		  </xsl:when>
		  <xsl:otherwise>
			<input type="checkbox" name="is_on_list_display_{ascii_id}"/>
		  </xsl:otherwise>
		</xsl:choose>
	  </td>
	  <td>
		<xsl:choose>
		  <xsl:when test="is_public = 1">
			<input type="checkbox" name="is_public_{ascii_id}" checked="checked"/>
		  </xsl:when>
		  <xsl:otherwise>
			<input type="checkbox" name="is_public_{ascii_id}"/>
		  </xsl:otherwise>
		</xsl:choose>
	  </td>
	</tr>
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
