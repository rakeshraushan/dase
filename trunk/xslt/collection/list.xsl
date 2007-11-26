<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="xml" indent="yes" encoding="UTF-8"/>
  <xsl:preserve-space elements="*"/>
  <!-- include general stylesheet -->
  <xsl:include href="../site/stylesheet.xsl"/>
  <!-- use services to get any needed content -->
  <xsl:variable name="collections-list" select="document($src)/collections"/>

  <xsl:template match="insert-collection-list-items">
	<xsl:apply-templates select="$collections-list"/>
  </xsl:template>

  <xsl:template match="collections">
	<xsl:apply-templates select="collection"/>
  </xsl:template>

  <xsl:template match="collection">
	  <xsl:text>
	  </xsl:text>
	<li id="{@ascii_id}">
	  <input name="c" value="{@ascii_id}" checked="checked" type="checkbox"/>
	  <xsl:text> </xsl:text>
	  <a href="{@ascii_id}" class="checkedCollection"><xsl:value-of select="@collection_name"/></a>
	  <xsl:text> </xsl:text>
	  <span class="tally"></span>
	</li>
  </xsl:template>

</xsl:stylesheet>
