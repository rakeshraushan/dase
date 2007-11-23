<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns:a="http://www.w3.org/2005/Atom"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  >
  <xsl:output method="xml" encoding="UTF-8" indent="yes"/>
  <xsl:preserve-space elements="*"/>
  <!-- use services to get any needed content -->
  <xsl:variable name="source" select="document($src)"/>

  <xsl:template match="/">
	<xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="feed">
	<a:feed>
	  <xsl:apply-templates select="$source/attribute"/>
	</a:feed>
  </xsl:template>

  <xsl:template match="attribute">
	<a:title>
	  <xsl:value-of select="@attribute_name"/>
	</a:title>
	<a:id>
	  <xsl:value-of select="concat($app_root,@collection,'/att/',@ascii_id,'/')"/>
	</a:id>
	<a:updated><xsl:value-of select="@updated"/></a:updated>
	<a:generator uri="http://daseproject.org" version="1.0">DASe</a:generator>
	<a:link rel="self" type="application/atom+xml" href="{concat($app_root,'atom/',@collection,'/att/',@ascii_id,'/')}"/>
	<a:link rel="alternate" type="application/xhtml+xml" href="{concat($app_root,'/html/',@collection,'/att/',@ascii_id,'/')}"/>
	<a:author><a:name>DASe</a:name></a:author>
	<a:entry>
	  <a:title><xsl:value-of select="@attribute_name"/></a:title>
	  <a:id><xsl:value-of select="concat($app_root,@collection,'/att/',@ascii_id,'/')"/></a:id>
	  <a:category term="attribute" scheme="http://daseproject.org/category" label="Attribute"/>
	  <a:updated><xsl:value-of select="@updated"/></a:updated>
	  <a:link type="application/xhtml+xml" href="{concat($app_root,'html/',@collection,'/att/',@ascii_id,'/')}"/>
	  <xsl:apply-templates select="defined_values"/>
	</a:entry>
  </xsl:template>

  <xsl:template match="defined_values">
	<a:content type="xhtml">
	  <div>
		<p>defined values</p>
		<ul>
		  <xsl:apply-templates select="defined_value"/>
		</ul>
	  </div>
	</a:content>
  </xsl:template>

  <xsl:template match="defined_value">
	<li><xsl:value-of select="@value_text"/></li>
  </xsl:template>

  <!-- Identity transformation -->
  <xsl:template match="@*|*">
	<xsl:copy>
	  <xsl:apply-templates select="@*|node()"/>
	</xsl:copy>
  </xsl:template>

</xsl:stylesheet>
