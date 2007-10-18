<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="xml" 
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" 
	encoding="UTF-8"/>
  <!-- include general stylesheet -->
  <xsl:include href="stylesheet.xsl"/>
  <!-- use services to get any needed content -->
  <xsl:variable name="collections-list" select="document($collections)/collections"/>

  <xsl:template match="insert-collection-list-items">
	<xsl:apply-templates select="$collections-list" mode="coll-list"/>
  </xsl:template>

  <xsl:template match="collection" mode="coll-list">
	<li id="{@ascii_id}">
	  <input name="cols[]" value="{@id}" checked="checked" type="checkbox"/>
	  <a href="{@ascii_id}" class="checkedCollection"><xsl:value-of select="@collection_name"/></a>
	  <span class="tally"></span>
	</li>
  </xsl:template>

</xsl:stylesheet>
