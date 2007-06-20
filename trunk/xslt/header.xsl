<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	version="1.0">
	<xsl:output method="xml" indent="yes"/>

	<xsl:template match="/">
		<xsl:text disable-output-escaping="yes">&lt;!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
			"http://www.w3c.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"&gt;</xsl:text>
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
			<head>
				<title>Collections</title>
				<meta name="description" content="The Digital Archive Services project is a collaborative slideshow application created by the College of Liberal Arts at the University of Texas at Austin." />
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<base id="base_href" href="http://littlehat.com/mvc/"/>
				<link rel="stylesheet" type="text/css" href="css/css.php"/>
				<script type="text/javascript" src="js/dase.js"></script>
				<script type="text/javascript" src="js/dase_editable.js"></script>
				<link rel="shortcut icon" href="http://www.laits.utexas.edu/dase/favicon.ico"/>
				<noscript>
					<style type="text/css">
						{literal}
						.tempHide {
						display:block;
						}
						.noScriptHide {
						display: none;
						}
						{/literal}
					</style>
				</noscript>
			</head>
			<body>
				<div class="cbBanner">
					DASE 
					<p>LittleHat.com</p>
				</div>
				<div id="sidebar">
					<ul id="menuNav">
						<li class="search"><a href=".">Home/Search</a></li>
						<li class="collections" id="collections"><a href="">My Collections</a></li>
						<ul id="collections-sub" class="tempHide">
							<a href="action/create_tag_form/user_collection" onclick="return createTag(2,'User Collection');" class="create">create new collection</a>
						</ul>
						<li class="slideshows" id="slideshows"><a href="">My Slideshows</a></li>
						<ul id="slideshows-sub" class="tempHide">
							<a href="action/create_tag_form/slideshow" onclick="return createTag(3,'Slideshow');" class="create">create new slideshow</a>
						</ul>
					</ul>
				</div> 
				<xsl:template match="collections">
					<ul>
						<xsl:apply-templates/>
					</ul>
				</xsl:template>
			</body>
		</html>
	</xsl:template>

	<xsl:template match="collection">
		<li><a href="{@ascii_id}/items"><xsl:value-of select="@collection_name"/></a></li>
	</xsl:template>

	<!-- Copy all the other elements and attributes, and text nodes -->
	<xsl:template match="*|@*|text()">
		<xsl:copy>
			<xsl:apply-templates select="*|@*|text()"/>
		</xsl:copy>
	</xsl:template>
</xsl:stylesheet>

