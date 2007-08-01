<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	version="1.0">
	<xsl:output method="xml"/>
	<xsl:template match="side">
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
		<xsl:apply-templates/>
	</xsl:template>

	<!-- Copy all the other elements and attributes, and text nodes -->
	<xsl:template match="*|@*|text()">
		<xsl:copy>
			<xsl:apply-templates select="*|@*|text()"/>
		</xsl:copy>
	</xsl:template>
</xsl:stylesheet>

