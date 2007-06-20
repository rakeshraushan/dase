<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	version="1.0">
	<xsl:template match="collections">
		<ul>
			<xsl:apply-templates/>
		</ul>
	</xsl:template>

	<xsl:template match="collection">
		<li><a href="{@ascii_id}/items"><xsl:value-of select="@collection_name"/></a></li>
	</xsl:template>

	<!-- Copy all the other elements and attributes, and text nodes -->
	<xsl:template match="*|@*|text()">
		<xsl:copy>
			<xsl:apply-templates select="*|@*|text()"/>
		</xsl:copy>
	</xsl:template>
</xsl:stylesheet>

