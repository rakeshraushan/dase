<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<!-- from http://biglist.com/lists/lists.mulberrytech.com/xsl-list/archives/200510/msg00546.html -->
<xsl:template name="lf2br">
  <!-- import $StringToTransform -->
  <xsl:param name="StringToTransform"/>
  <xsl:choose>
	<!-- string contains linefeed -->
	<xsl:when test="contains($StringToTransform,'&#xA;')">
	  <!-- output substring that comes before the first linefeed -->
	  <!-- note: use of substring-before() function means        -->
	  <!-- $StringToTransform will be treated as a string,       -->
	  <!-- even if it is a node-set or result tree fragment.     -->
	  <!-- So hopefully $StringToTransform is really a string!   -->
	  <xsl:value-of select="substring-before($StringToTransform,'&#xA;')"/>
	  <!-- by putting a 'br' element in the result tree instead  -->
	  <!-- of the linefeed character, a <br> will be output at   -->
		<!-- that point in the HTML                                -->
		<br/>
		<!-- repeat for the remainder of the original string -->
		<xsl:call-template name="lf2br">
		  <xsl:with-param name="StringToTransform">
			<xsl:value-of select="substring-after($StringToTransform,'&#xA;')"/>
		  </xsl:with-param>
		</xsl:call-template>
	  </xsl:when>
	  <!-- string does not contain newline, so just output it -->
	  <xsl:otherwise>
		<xsl:value-of select="$StringToTransform"/>
	  </xsl:otherwise>
	</xsl:choose>
  </xsl:template>

  <xsl:template name="CopyWithLineBreaks">
	<xsl:param name="string"/>
	<xsl:variable name="Result">
	  <xsl:call-template name="lf2br">
		<xsl:with-param name="StringToTransform" select="$string"/>
	  </xsl:call-template>
	</xsl:variable>
	<xsl:copy-of select="$Result"/>
  </xsl:template>

</xsl:stylesheet>
