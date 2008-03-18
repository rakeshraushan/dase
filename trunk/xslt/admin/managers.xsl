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
	<h1 id="pageHeader">Managers for <xsl:value-of select="$collection/collection_name/text()"/></h1>
	<!--
	<h2><xsl:value-of select="$user/ppd"/></h2>
	-->
	<div id="collectionData">
	  <table id="dataDisplay">
		<tr>
		  <th>Name</th>
		  <th>Eid</th>
		  <th>Auth Level</th>
		  <th>Expiration</th>
		  <th>Created</th>
		</tr>
		<xsl:apply-templates select="$collection/manager" mode="coll"/>
	  </table>
	</div>
  </xsl:template>


  <xsl:template match="manager" mode="coll">
	<tr>
	  <th class="rows">
		<xsl:value-of select="name"/>
	  </th>
	  <td>
		<xsl:value-of select="dase_user_eid"/>
	  </td>
	  <td>
		<xsl:value-of select="auth_level"/>
	  </td>
	  <td>
		<xsl:value-of select="expiration"/>
	  </td>
	  <td>
		<xsl:value-of select="created"/>
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
