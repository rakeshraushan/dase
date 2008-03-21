<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="text"/>
  <xsl:strip-space elements="*"/>
  <xsl:variable name="borderLevel">1</xsl:variable>

  <xsl:template match="/">
	#sidebar
	{
	width: 16%;
	background-color: #ffffff;
	margin: 0px 3px 0px 0px;
	font-size: 92%;
	float:left;
	}

	ul#menu a {
	display:block;
	padding: 5px;
	margin: 2px 0px;
	}
	ul#menu  li  {
	/* fixes ie problem */
	display:inline;
	}
	ul#menu  li  ul {
	margin-left:18px;
	}
	ul#menu  li ul li ul {
	padding:6px;
	margin-left:0px;
	background-color: #eee;
	}
	ul#menu  input {
	margin: 2px 0;
	}
	ul#menu li ul li a{
	padding:6px;
	margin: 0px;
	border-top: 1px solid #fff;

	}
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

</xsl:stylesheet>
