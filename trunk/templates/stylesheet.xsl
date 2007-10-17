<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="xml" 
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" 
	encoding="UTF-8"/>
		<xsl:variable name="page" select="/"/>
		<xsl:variable name="layout" select="document('layout.xml')"/>

		<xsl:template match="/">
			<xsl:apply-templates select="$layout/html"/>
		</xsl:template>

		<xsl:template match="insert-base-href">
		  <base href="{$app_root}"/>
		</xsl:template>

		<xsl:template match="insert-timer">
		  <xsl:value-of select="$timer"/>	
		</xsl:template>

		<xsl:template match="insert-title">
		  <xsl:apply-templates select="$page/html/head/title"/>
		</xsl:template>

		<xsl:template match="insert-content">
		  <!--<xsl:apply-templates select="$page/html/body/div[@class=specific-content]/*"/>-->
		  <xsl:apply-templates select="$page/html/body/*"/>
		</xsl:template>

		<xsl:template match="insert-collection-list-items">
		  <xsl:apply-templates select="document(@href)/collections" mode="coll-list"/>
		</xsl:template>

		<xsl:template match="collection" mode="coll-list">
		  <li id="{@ascii_id}">
			<input name="cols[]" value="{@id}" checked="checked" type="checkbox"/>
			<a href="{@ascii_id}" class="checkedCollection"><xsl:value-of select="@collection_name"/></a>
			<span class="tally"></span>
		  </li>
		</xsl:template>

		<!-- Identity transformation -->
		<xsl:template match="@*|*">
		  <xsl:copy>
			<xsl:apply-templates select="@*|node()"/>
		  </xsl:copy>
		</xsl:template>

	  </xsl:stylesheet>
