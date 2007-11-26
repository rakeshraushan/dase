<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/2005/Atom"
  xmlns:d="http://daseproject.org"
  xmlns:opensearch="http://a9.com/-/spec/opensearch/1.1/"
  >
  <xsl:output method="xml" encoding="UTF-8" indent="yes"/>
  <xsl:preserve-space elements="*"/>

  <xsl:template match="item">
	<entry>
	  <title><xsl:value-of select="text()"/></title>
	  <updated><xsl:value-of select="@last_update"/></updated>
	  <id><xsl:value-of select="concat($app_root,@collection_ascii_id,'/',@serial_number)"/></id>
	  <link rel="self" type="application/atom+xml" href="{concat($app_root,'atom/',@collection_ascii_id,'/',@serial_number)}"/>
	  <link rel="alternate" type="application/xhtml+xml" href="{concat($app_root,'html/',@collection_ascii_id,'/',@serial_number)}"/>
	  <!-- there if statements need to be redone as templates-->
	  <xsl:if test="search_link">
		<link rel="http://daseproject.org/relation/search-link" type="application/xhtml+xml" href="{concat($app_root,search_link/@url)}"/>
	  </xsl:if>
	  <xsl:if test="search_item_link">
		<link rel="http://daseproject.org/relation/search-item-link" type="application/xhtml+xml" href="{concat($app_root,search_item_link/@url)}"/>
	  </xsl:if>
	  <category term="{@id}" scheme="http://daseproject.org/category/item_id" label="{@id}"/>
	  <category term="{@collection_ascii_id}" scheme="http://daseproject.org/category/collection" label="{@collection_name}"/>
	  <xsl:if test="search_index/text()">
		<category term="{search_index/text()}" scheme="http://daseproject.org/category/search_result/index" label="{search_index/text()}"/>
		<category term="search_result_item" scheme="http://daseproject.org/category" label="search_result_item"/>
	  </xsl:if>
	  <xsl:apply-templates select="metadata_set"/>
	  <xsl:apply-templates select="media_files/media_file"/>
	</entry>
  </xsl:template>

  <xsl:template match="metadata_set">
	<content type="xhtml">
	  <div xmlns="http://www.w3.org/1999/xhtml">
		<dl>
		  <!-- from http://www.biglist.com/lists/xsl-list/archives/200206/msg01352.html-->
		  <!-- also j.tennison xslt on the edge p. 438 -->
		  <xsl:attribute name="dummy" namespace="{concat($app_root,../@collection_ascii_id,'/')}" />
		  <!--foreach used instead of tempate for namespacing reasons-->
		  <xsl:for-each select="metadata">
			<dt>
			  <xsl:value-of select="@attribute_name"/>
			</dt>
			<dd property="{concat('ns1:',@attribute_ascii_id)}">
			  <xsl:value-of select="@value_text"/>
			</dd>
		  </xsl:for-each>
		</dl>
		<img src="{../media_files/media_file[@size='thumbnail']/@url}"/>
		<p><xsl:value-of select="../@collection_name"/></p>
	  </div>
	</content>
  </xsl:template>

  <xsl:template match="media_file">
	<link rel="{concat('http://daseproject.org/relation/media/',@size)}" href="{@url}" d:size="{@size}" d:width="{@width}" d:height="{@height}"/>
  </xsl:template>
</xsl:stylesheet>
