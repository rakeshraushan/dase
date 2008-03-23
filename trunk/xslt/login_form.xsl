<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:h="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="h"
  >
  <xsl:output method="xml" 
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" 
	encoding="UTF-8"/>

  <xsl:template match="/">
	<xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="insert-page-hook">
	<xsl:value-of select="$page_hook"/>
  </xsl:template>

  <xsl:template match="insert-base-href">
	<base href="{$app_root}"/>
  </xsl:template>

  <xsl:template match="insert-title">
	<title>Login Form</title>
  </xsl:template>

  <xsl:template match="insert-content">
	<content>
	  <div class="list" id="browse">
		<div class="alert"><xsl:value-of select="$msg"/></div>
		<h1>Please Login to Dase:</h1>
		<form id="loginForm" action="login" method="post">
		  <p>
			<label for="username-input">username:</label>
			<input type="text" id="username-input" name="username"/>
		  </p>
		  <p>
			<label for="password-input">password:</label>
			<input type="password" id="password-input" name="password"/>
		  </p>
		  <p>
			<input type="submit" value="login"/>
		  </p>
		</form>
	  </div>
	</content>
  </xsl:template>

  <xsl:template match="dynamic"/>

  <!-- Identity transformation -->
  <xsl:template match="@*|*">
	<xsl:copy>
	  <xsl:apply-templates select="@*|node()"/>
	</xsl:copy>
  </xsl:template>

</xsl:stylesheet>
