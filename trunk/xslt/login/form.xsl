<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:include href="../site/stylesheet.xsl"/>

  <xsl:template match="insert-msg">
	<div class="alert"><xsl:value-of select="$msg"/></div>
  </xsl:template>

</xsl:stylesheet>
