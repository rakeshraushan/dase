<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="text" 
	encoding="UTF-8"
	indent="no"
	/>
  <xsl:strip-space elements="*"/>

  <xsl:template match="/">
	<xsl:text>&lt;?php 
	  $routes = array(
	  "get" => array(
	</xsl:text>
	<xsl:apply-templates mode="get"/>
	'end' => ''
	),
	"post" => array(
	<xsl:apply-templates mode="post"/>
	'end' => ''
	),
	"put" => array(
	<xsl:apply-templates mode="put"/>
	'end' => ''
	),
	"delete" => array(
	<xsl:apply-templates mode="delete"/>
	'end' => ''
	)
	);
  </xsl:template>

  <xsl:template match="route" mode="get">
	<xsl:if test="method/text()='get'">
	  "<xsl:value-of select="match/text()"/>" => array(<xsl:text/>	
	  <xsl:apply-templates/>
	  'end' => ''
	  ),
	</xsl:if>
  </xsl:template>

  <xsl:template match="route" mode="post">
	<xsl:if test="method/text()='post'">
	  "<xsl:value-of select="match/text()"/>" => array(<xsl:text/>
	  <xsl:apply-templates/>
	  'end' => ''
	  ),
	</xsl:if>
  </xsl:template>

  <xsl:template match="route" mode="put">
	<xsl:if test="method/text()='put'">
	  "<xsl:value-of select="match/text()"/>" => array(<xsl:text/>
	  <xsl:apply-templates/>
	  'end' => ''
	  ),
	</xsl:if>
  </xsl:template>

  <xsl:template match="route" mode="delete">
	<xsl:if test="method/text()='delete'">
	  "<xsl:value-of select="match/text()"/>" => array(<xsl:text/>
	  <xsl:apply-templates/>
	  'end' => ''
	  ),
	</xsl:if>
  </xsl:template>

  <xsl:template match="auth|mime|nocache|collection|name|caps|params|action|handler|prefix">
	<xsl:if test="text()">
	  '<xsl:value-of select="local-name(.)"/>' => "<xsl:value-of select="."/>",<xsl:text/>	
	</xsl:if>
  </xsl:template>

  <xsl:template match="method|match"/>


</xsl:stylesheet>
