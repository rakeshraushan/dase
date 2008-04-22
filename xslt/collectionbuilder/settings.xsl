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
	<div id="contentHeader">
	  <h1>Collection Settings for <xsl:value-of select="$collection/collection_name/text()"/></h1>
	  <!--
	  <h2><xsl:value-of select="$user/ppd"/></h2>
	  -->
	</div>
	<div id="collectionData">
	  <table id="dataDisplay">
		<tr>
		  <th>Name</th>
		  <th>Ascii Id</th>
		  <th>Is Public</th>
		  <th>Description</th>
		  <th>Created</th>
		  <th>Path to Media Files</th>
		</tr>
		<xsl:apply-templates select="$collection" mode="coll"/>
	  </table>
	</div>
  </xsl:template>


  <xsl:template match="collection" mode="coll">
	<tr>
	  <th>
		<xsl:value-of select="collection_name"/>
	  </th>
	  <td>
		<xsl:value-of select="ascii_id"/>
	  </td>
	  <td>
		<xsl:choose>
		  <xsl:when test="is_public/text() = 1">yes</xsl:when>
		  <xsl:otherwise>no</xsl:otherwise>
		</xsl:choose>
	  </td>
	  <td>
		<xsl:value-of select="description"/>
	  </td>
	  <td>
		<xsl:value-of select="created"/>
	  </td>
	  <td>
		<xsl:value-of select="path_to_media_files"/>
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
