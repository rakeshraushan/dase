<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:a="http://www.w3.org/2005/Atom"
  xmlns:d="http://daseproject.org"
  xmlns:opensearch="http://a9.com/-/spec/opensearch/1.1/"
  xmlns="http://www.w3.org/1999/xhtml"
  >
  <xsl:output method="xml" encoding="UTF-8" indent="yes"/>
  <xsl:preserve-space elements="*"/>

  <xsl:template match="item">
	<a:entry>
	  <a:title><xsl:value-of select="text()"/></a:title>
	  <a:updated><xsl:value-of select="@last_update"/></a:updated>
	  <a:id><xsl:value-of select="concat($app_root,@collection_ascii_id,'/',@serial_number)"/></a:id>
	  <a:link rel="self" type="application/atom+xml" href="{concat($app_root,'atom/',@collection_ascii_id,'/',@serial_number)}"/>
	  <a:link rel="alternate" type="application/xhtml+xml" href="{concat($app_root,'html/',@collection_ascii_id,'/',@serial_number)}"/>
	  <!-- there if statements need to be redone as templates-->
	  <xsl:if test="search_link">
		<a:link rel="http://daseproject.org/relation/search-link" type="application/xhtml+xml" href="{concat($app_root,search_link/@url)}"/>
	  </xsl:if>
	  <xsl:if test="search_item_link">
		<a:link rel="http://daseproject.org/relation/search-item-link" type="application/xhtml+xml" href="{concat($app_root,search_item_link/@url)}"/>
	  </xsl:if>
	  <a:category term="{@id}" scheme="http://daseproject.org/category/item_id" label="{@id}"/>
	  <a:category term="{@collection_ascii_id}" scheme="http://daseproject.org/category/collection" label="{@collection_name}"/>
	  <xsl:if test="search_index/text()">
		<a:category term="{search_index/text()}" scheme="http://daseproject.org/category/search_result/index" label="{search_index/text()}"/>
		<a:category term="search_result_item" scheme="http://daseproject.org/category" label="search_result_item"/>
	  </xsl:if>
	  <xsl:apply-templates select="metadata_set"/>
	  <xsl:apply-templates select="media_files/media_file"/>
	</a:entry>
  </xsl:template>

  <!-- fix this to be cleaner!!-->
  <xsl:template match="metadata_set">
	<xsl:param name="ns" select="concat($app_root,../@collection_ascii_id)"/>
	<a:content type="xhtml">
	  <div>
		<dl>
		  <!-- from http://www.biglist.com/lists/xsl-list/archives/200206/msg01352.html-->
		  <xsl:attribute name="dummy" namespace="{concat($app_root,../@collection_ascii_id)}" />
		  <xsl:apply-templates select="metadata"/>
		</dl>
		<img src="{../media_files/media_file[@size='thumbnail']/@url}"/>
		<p><xsl:value-of select="../@collection_name"/></p>
	  </div>
	</a:content>
  </xsl:template>


  <xsl:template match="metadata">
	<dt><xsl:value-of select="@attribute_name"/></dt>
	<dd property="{concat('ns1:',@attribute_ascii_id)}">
	  <xsl:value-of select="@value_text"/>
	</dd>
  </xsl:template>

  <xsl:template match="media_file">
	<a:link rel="{concat('http://daseproject.org/relation/media/',@size)}" href="{@url}" d:size="{@size}" d:width="{@width}" d:height="{@height}"/>
  </xsl:template>
</xsl:stylesheet>
