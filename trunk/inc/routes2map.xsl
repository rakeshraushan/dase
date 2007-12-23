<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="xml" 
	encoding="UTF-8"
	indent="yes"
	/>

  <xsl:strip-space elements="*"/>

  <xsl:template match="/">
	<xsl:element name="routes">
	  <xsl:apply-templates/>
	  <xsl:apply-templates select="document('modules.xml')/modules"/>
	</xsl:element>
  </xsl:template>

  <xsl:template match="/" mode="modules">
	<xsl:param name="collection"/>
	<xsl:param name="name"/>
	<xsl:apply-templates>
	  <xsl:with-param name="collection" select="$collection"/>
	  <xsl:with-param name="name" select="$name"/>
	</xsl:apply-templates>
  </xsl:template>

  <xsl:template match="module">
	<xsl:param name="collection" select="@collection"/>
	<xsl:param name="name" select="@name"/>
	<xsl:apply-templates select="document(concat('../modules/',@name,'/routes.xml'))" mode="modules">
	  <xsl:with-param name="collection" select="$collection"/>
	  <xsl:with-param name="name" select="$name"/>
	</xsl:apply-templates>
  </xsl:template>

  <xsl:template match="route">
	<!-- may contain these attributes -->
	<xsl:param name="action" select="@action"/>
	<xsl:param name="auth" select="@auth"/>
	<xsl:param name="mime" select="@mime"/>
	<xsl:param name="nocache" select="@nocache"/>
	<xsl:param name="params" select="@params"/>
	<xsl:param name="method">
	  <!-- if no method, use 'get' -->
	  <xsl:choose>
		<xsl:when test="@method">
		  <xsl:value-of select="@method"/>
		</xsl:when>
		<xsl:otherwise>
		  <xsl:value-of select="'get'"/>
		</xsl:otherwise>
	  </xsl:choose>
	</xsl:param>
	<!-- params from module routes -->
	<xsl:param name="collection"/>
	<xsl:param name="name"/>
	<xsl:apply-templates>
	  <xsl:with-param name="action" select="$action"/>
	  <xsl:with-param name="params" select="$params"/>
	  <xsl:with-param name="auth" select="$auth"/>
	  <xsl:with-param name="mime" select="$mime"/>
	  <xsl:with-param name="nocache" select="$nocache"/>
	  <xsl:with-param name="method" select="$method"/>
	  <xsl:with-param name="collection" select="$collection"/>
	  <xsl:with-param name="name" select="$name"/>
	</xsl:apply-templates>
	<!-- handle case where no match is explicitly set -->
	<xsl:if test="not(child::node())">
	  <xsl:element name="route">
		<xsl:element name="match"><xsl:value-of select="$action"/></xsl:element>
		<xsl:element name="action"><xsl:value-of select="$action"/></xsl:element>
		<xsl:element name="method"><xsl:value-of select="$method"/></xsl:element>
		<xsl:element name="auth"><xsl:value-of select="$auth"/></xsl:element>
		<xsl:element name="mime"><xsl:value-of select="$mime"/></xsl:element>
		<xsl:element name="nocache"><xsl:value-of select="$nocache"/></xsl:element>
		<xsl:element name="params"><xsl:value-of select="$params"/></xsl:element>
		<xsl:element name="collection"><xsl:value-of select="$collection"/></xsl:element>
		<xsl:element name="name"><xsl:value-of select="$name"/></xsl:element>
	  </xsl:element>
	  <xsl:text>
	  </xsl:text>
	</xsl:if>
  </xsl:template>

  <xsl:template match="match">
	<!-- we'll always get action, auth & method from invoking template -->
	<xsl:param name="action"/>
	<xsl:param name="auth"/>
	<xsl:param name="mime"/>
	<xsl:param name="nocache"/>
	<xsl:param name="method"/>
	<xsl:param name="params"/>
	<xsl:param name="collection"/>
	<xsl:param name="name"/>
	<xsl:param name="caps" select="@caps"/>
	<xsl:param name="match" select="."/>

	<!-- we might get  params from invoking template-->
	<xsl:param name="local-params">
	  <xsl:choose>
		<xsl:when test="$params">
		  <xsl:value-of select="$params"/>
		</xsl:when>
		<xsl:otherwise>
		  <xsl:value-of select="@params"/>
		</xsl:otherwise>
	  </xsl:choose>
	</xsl:param>
	<xsl:element name="route">
	  <xsl:element name="match">
		<xsl:text>^</xsl:text>
		<xsl:call-template name="construct-match">
		  <xsl:with-param name="match" select="$match"/>
		  <xsl:with-param name="name" select="$name"/>
		  <xsl:with-param name="local-params" select="$local-params"/>
		</xsl:call-template>
		<xsl:text>$</xsl:text>
	  </xsl:element>
	  <xsl:element name="action"><xsl:value-of select="$action"/></xsl:element>
	  <xsl:element name="method"><xsl:value-of select="$method"/></xsl:element>
	  <xsl:element name="auth"><xsl:value-of select="$auth"/></xsl:element>
	  <xsl:element name="mime"><xsl:value-of select="$mime"/></xsl:element>
	  <xsl:element name="nocache"><xsl:value-of select="$nocache"/></xsl:element>
	  <xsl:element name="params"><xsl:value-of select="$local-params"/></xsl:element>
	  <xsl:element name="collection"><xsl:value-of select="$collection"/></xsl:element>
	  <xsl:element name="name"><xsl:value-of select="$name"/></xsl:element>
	  <xsl:element name="caps"><xsl:value-of select="$caps"/></xsl:element>
	  <xsl:if test="$name">
		<xsl:element name="prefix"><xsl:value-of select="concat('/modules/',$name)"/></xsl:element>
	  </xsl:if>
	</xsl:element>
	<xsl:text>
	</xsl:text>
  </xsl:template>

  <xsl:template name="construct-match">
	<xsl:param name="match"/>
	<xsl:param name="name"/>
	<xsl:param name="local-params"/>
	<xsl:if test="$name">
	  <xsl:value-of select="concat('modules/',$name)"/>
	  <xsl:if test="$match!=''">
		<xsl:value-of select="'/'"/>
	  </xsl:if>
	</xsl:if>
	<xsl:value-of select="$match"/>
	<xsl:call-template name="construct-regex">
	  <xsl:with-param name="local-params" select="$local-params"/>
	</xsl:call-template>
  </xsl:template>

  <xsl:template name="construct-regex">
	<xsl:param name="regex"/>
	<xsl:param name="local-params"/>
	<xsl:variable name="tmpl" select="'/([^/]*)'"/>
	<xsl:choose>
	  <xsl:when test="string-length($local-params) and contains($local-params,'/')">
		<!-- output a regex template -->
		<xsl:value-of select="$tmpl"/>
		<xsl:variable name="rest" select="substring-after($local-params,'/')"/>
		<xsl:call-template name="construct-regex">
		  <xsl:with-param name="local-params" select="$rest"/>
		</xsl:call-template>
	  </xsl:when>
	  <xsl:when test="string-length($local-params)">
		<!-- output a regex template -->
		<xsl:value-of select="$tmpl"/>
	  </xsl:when>
	  <xsl:otherwise></xsl:otherwise>
	</xsl:choose>
  </xsl:template>

</xsl:stylesheet>



