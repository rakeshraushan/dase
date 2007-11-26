<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns="http://www.w3.org/2005/Atom"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  >
  <xsl:output method="xml" encoding="UTF-8" indent="yes"/>
  <xsl:preserve-space elements="*"/>
  <!-- use services to get any needed content -->
  <xsl:variable name="source" select="document($src)"/>

  <xsl:template match="/">
	<xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="feed">
	<feed>
	  <xsl:apply-templates select="$source/attribute"/>
	</feed>
  </xsl:template>

  <xsl:template match="attribute">
	<title>
	  <xsl:value-of select="@attribute_name"/>
	</title>
	<id>
	  <xsl:value-of select="concat($app_root,@collection,'/att/',@ascii_id,'/')"/>
	</id>
	<updated><xsl:value-of select="@updated"/></updated>
	<generator uri="http://daseproject.org" version="1.0">DASe</generator>
	<link rel="self" type="application/atom+xml" href="{concat($app_root,'atom/',@collection,'/att/',@ascii_id,'/')}"/>
	<link rel="alternate" type="application/xhtml+xml" href="{concat($app_root,'/html/',@collection,'/att/',@ascii_id,'/')}"/>
	<author><name>DASe</name></author>
	<entry>
	  <title><xsl:value-of select="@attribute_name"/></title>
	  <id><xsl:value-of select="concat($app_root,@collection,'/att/',@ascii_id,'/')"/></id>
	  <category term="attribute" scheme="http://daseproject.org/category" label="Attribute"/>
	  <updated><xsl:value-of select="@updated"/></updated>
	  <link type="application/xhtml+xml" href="{concat($app_root,'html/',@collection,'/att/',@ascii_id,'/')}"/>
	  <xsl:apply-templates select="defined_values"/>
	</entry>
  </xsl:template>

  <xsl:template match="defined_values">
	<content type="xhtml">
	  <div xmlns="http://www.w3.org/1999/xhtml">
		<p>defined values</p>
		<ul>
		  <xsl:for-each select="defined_value">
			<li><xsl:value-of select="@value_text"/></li>
		  </xsl:for-each>
		</ul>
	  </div>
	</content>
  </xsl:template>

  <!-- Identity transformation -->
  <xsl:template match="@*|*">
	<xsl:copy>
	  <xsl:apply-templates select="@*|node()"/>
	</xsl:copy>
  </xsl:template>

</xsl:stylesheet>
