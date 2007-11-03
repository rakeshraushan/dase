<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="text" 
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" 
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
	),
	"post" => array(
	<xsl:apply-templates mode="post"/>
	),
	"put" => array(
	<xsl:apply-templates mode="put"/>
	),
	"delete" => array(
	<xsl:apply-templates mode="delete"/>
	)
	);
	<xsl:apply-templates select="document('modules.xml')/modules"/>
  </xsl:template>

  <!-- top template for modules -->
  <xsl:template match="/" mode="modules">
	<xsl:text> 
	  $routes = array(
	  "get" => array(
	</xsl:text>
	<xsl:apply-templates mode="get">
	  <xsl:with-param name="collection" select="$collection"/>
	  <xsl:with-param name="name" select="$name"/>
	</xsl:apply-templates>
	),
	"post" => array(
	<xsl:apply-templates mode="post">
	  <xsl:with-param name="collection" select="$collection"/>
	  <xsl:with-param name="name" select="$name"/>
	</xsl:apply-templates>
	),
	"put" => array(
	<xsl:apply-templates mode="put">
	  <xsl:with-param name="collection" select="$collection"/>
	  <xsl:with-param name="name" select="$name"/>
	</xsl:apply-templates>
	),
	"delete" => array(
	<xsl:apply-templates mode="delete">
	  <xsl:with-param name="collection" select="$collection"/>
	  <xsl:with-param name="name" select="$name"/>
	</xsl:apply-templates>
	)
	);
  </xsl:template>

  <xsl:template match="module">
	<xsl:param name="collection" select="@collection"/>
	<xsl:param name="name" select="@name"/>
	<xsl:apply-templates select="document(concat('../modules/',@name,'/inc/routes.xml'))" mode="modules">
	  <xsl:with-param name="collection" select="$collection"/>
	  <xsl:with-param name="name" select="$name"/>
	</xsl:apply-templates>
  </xsl:template>

  <xsl:template match="route" mode="get">
	<xsl:param name="collection"/>
	<xsl:param name="name"/>
	<xsl:if test="not(@method) or @method='get'">
	  <xsl:apply-templates>
	  <xsl:with-param name="collection" select="$collection"/>
	  <xsl:with-param name="name" select="$name"/>
	</xsl:apply-templates>
	</xsl:if>
  </xsl:template>

  <xsl:template match="route" mode="post">
	<xsl:param name="collection"/>
	<xsl:param name="name"/>
	<xsl:if test="@method='post'">
	  <xsl:apply-templates>
	  <xsl:with-param name="collection" select="$collection"/>
	  <xsl:with-param name="name" select="$name"/>
	</xsl:apply-templates>
	</xsl:if>
  </xsl:template>

  <xsl:template match="route" mode="put">
	<xsl:param name="collection"/>
	<xsl:param name="name"/>
	<xsl:if test="@method='put'">
	  <xsl:apply-templates>
	  <xsl:with-param name="collection" select="$collection"/>
	  <xsl:with-param name="name" select="$name"/>
	</xsl:apply-templates>
	</xsl:if>
  </xsl:template>

  <xsl:template match="route" mode="delete">
	<xsl:param name="collection"/>
	<xsl:param name="name"/>
	<xsl:if test="@method='delete'">
	  <xsl:apply-templates>
	  <xsl:with-param name="collection" select="$collection"/>
	  <xsl:with-param name="name" select="$name"/>
	</xsl:apply-templates>
	</xsl:if>
  </xsl:template>

  <xsl:template match="match">
	<xsl:param name="collection"/>
	<xsl:param name="name"/>
	"^<xsl:value-of select="."/>$" => array(
	<xsl:if test="$name">"action" => "modules/<xsl:value-of select="$name"/>/<xsl:value-of select="../@action"/>",
	</xsl:if>
	<xsl:if test="not($name)">"action" => "<xsl:value-of select="../@action"/>",
	</xsl:if>
	<xsl:if test="../@auth">"auth" => "<xsl:value-of select="../@auth"/>",
	</xsl:if>
	<xsl:if test="not(../@auth)">"auth" => "user",
	</xsl:if>
	<xsl:if test="@params">"params" => "<xsl:value-of select="@params"/>",
	</xsl:if>
	<xsl:if test="@caps">"caps" => "<xsl:value-of select="@caps"/>",
	</xsl:if>
	<xsl:if test="$collection">"collection" => "<xsl:value-of select="$collection"/>",
	</xsl:if>
	<xml:text>"end" => "end"</xml:text>
	),
  </xsl:template>

</xsl:stylesheet>
