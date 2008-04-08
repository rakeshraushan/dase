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

  <xsl:template match="insert-menu"/>

  <xsl:template match="insert-content">
	<div class="full" id="settings">
	  <div id="contentHeader">
		<h1>Settings for <xsl:value-of select="$user/name"/></h1>
		<!--
		<h2><xsl:value-of select="$user/ppd"/></h2>
		-->
	  </div>
	  <h3>Managed Collections</h3>
	  <ul id="managedCollections">
		<xsl:apply-templates select="$user/managed_collections"/>
	  </ul>
	</div>
  </xsl:template>

  <xsl:template match="collection[@auth_level]">
	<li>
	  <xsl:value-of select="text()"/> (<xsl:value-of select="@auth_level"/>)
	  <a href="user/{$user/eid}/collection/{@ascii_id}/auth/read">read</a> |
	  <a href="user/{$user/eid}/collection/{@ascii_id}/auth/write">write</a> |
	  <a href="user/{$user/eid}/collection/{@ascii_id}/auth/admin">admin</a> 
	</li>
  </xsl:template>


</xsl:stylesheet>
