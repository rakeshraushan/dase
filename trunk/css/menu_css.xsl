<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="text"/>
	<xsl:strip-space elements="*"/>
	<xsl:variable name="borderLevel">1</xsl:variable>

	<xsl:template match="/">
		<xsl:copy-of select="document('sidebar_css.xml')/css"/>
		<xsl:apply-templates/>
	</xsl:template>

	<xsl:template match="hue">
li.<xsl:value-of select="@section"/> a.main {
border: 1px solid #<xsl:value-of select="hex[@level='1']"/>; 
border-left: 18px solid #<xsl:value-of select="hex[@level='1']"/>;
background-color: #ffffff;
}
li.<xsl:value-of select="@section"/> a:hover , a:active {
	background-color: #<xsl:value-of select="hex[@level='4']"/>;
}
ul#<xsl:value-of select="@section"/> a {
	background-color: #<xsl:value-of select="hex[@level='4']"/>;
}
ul#<xsl:value-of select="@section"/> a:hover {
	background-color: #<xsl:value-of select="hex[@level='5']"/>;
}

#<xsl:value-of select="@section"/> {
	background-color: #<xsl:value-of select="hex[@level='4']"/>;
}
</xsl:template>


<!--
	<xsl:template match="hex">
		<xsl:value-of select="parent::hue/attribute::id"/>
		<xsl:value-of select="@level"/>: #<xsl:apply-templates/>
		<xsl:text>&#xa;</xsl:text>
			</xsl:template>
			-->

</xsl:stylesheet>
