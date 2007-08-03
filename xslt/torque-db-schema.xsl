<?xml version="1.0" ?>
<!--
    Document: torque_db_schema.xsl
    Author  : mariusz(at)olejnik.net
    Version : 1.0, 2005-01-13
    Comment : add this line to xml schema file after <?xml version="1.0"/>: 
              <?xml-stylesheet type="text/xsl" href="torque-db-schema.xsl"?>
-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:output method="html" indent="yes"/>
<xsl:template match="/">
  <xsl:apply-templates/>
</xsl:template>

<xsl:template match="database">
<html>
  <head>
   <title> Torque Database Schema - <xsl:apply-templates select="@name"/></title>
   <style>
   body {
     font-family: verdana,MS Sans Serif,arial,helvetica;  
     font-size: 80%;	
     background-color: #FFFFFF; 
     color: #003399;
   }
   A:link, A:visited { 
     color:blue; 
     text-decoration:underline; 
   }
   A:hover { 
     color:#003399; 
     text-decoration:underline; 
   }
   table {
     font-family: verdana,MS Sans Serif,arial,helvetica;  
     padding: 1 1 1 1;
     margin: 1 1 1 1;
     font-size: 95%;
     width: 500px;
   }
   .rowTableName  { background-color: #003366; color: white; font-weight: bolder; text-align: center;}
   .colAttrName { background-color: #003366; color: white; width:20%; text-align: left;}
   .colAttrVal { background-color: #FFFFFF; color: black; width:80%; text-align: left;}
   .rowColumns { background-color: #C4E1FF; color: #003366; font-weight: bolder;}
   .rowAttrNorm { background-color: #EEEEEE; color: black;}
   .rowAttrReq { background-color: #EEEEEE; color: navy;}
   .rowAttrPK { background-color: #EEEEEE; color: red;}
   .rowFK { background-color: #888888; color: white; font-weight: bolder; text-align: center;}
   .rowFKItems { background-color: #FCFCFC; color: black;}
   .rowIX { background-color: #888888; color: white; font-weight: bolder; text-align: center;}
   .rowIXItems { background-color: #FCFCFC; color: black;}
  </style>
 </head>
<body>
  <xsl:apply-templates select="table"/>
</body>
</html>

</xsl:template>

<xsl:template match="table">
    <hr/>
	<table border="1" cellpadding="0" cellspacing="0" cols="2" align="center">
		<tr class="rowTableName"><td colspan="2"><a name="{@name}"><xsl:apply-templates select="@name"/></a> /<xsl:value-of select="@idMethod"/>,<xsl:value-of select="@javaName"/>/</td></tr>
		<tr>
			<td colspan="2">
				<table border="1" cellpadding="1" cellspacing="1" cols="6">
					<thead>
						<tr class="rowColumns">
							<td width="10px">Opt.</td>
							<td width="*">Column</td>
							<td width="100px">Type</td>
							<td width="30px">Size</td>
							<td width="30px">Default</td>
							<td width="100px">JavaType</td>
						</tr>
					</thead>
					<tbody>
						<xsl:apply-templates select="column"/>
					</tbody>
				</table>
			</td>
		</tr>

		<tr>
			<td colspan="2">
				<table border="1" cellpadding="1" cellspacing="1">
					<thead>
						<tr class="rowFK"><td colspan="3">Foreign keys</td></tr>
					</thead>
					<tbody>
						<xsl:apply-templates select="foreign-key"/>
					</tbody>
				</table>
			</td>
		</tr>
		
		<tr>
			<td colspan="2">
				<table border="1" cellpadding="1" cellspacing="1">
					<thead>
						<tr class="rowIX"><td>Indexes</td></tr>
					</thead>
					<tbody>
						<xsl:apply-templates select="index"/>
					</tbody>
				</table>
			</td>
		</tr>

        </table>
	<p/>
</xsl:template>

<xsl:template match="column">
	<xsl:choose>
		<xsl:when test="@primaryKey='true'">
			<tr class="rowAttrPK">
			<td>PK</td>
			<td><xsl:value-of select="@name"/></td>
			<td><xsl:value-of select="@type"/></td>
			<td><xsl:value-of select="@size"/>&#160;</td>
			<td><xsl:value-of select="@default"/>&#160;</td>
			<td><xsl:value-of select="@javaType"/>&#160;</td>
			</tr>
		</xsl:when>
		<xsl:when test="@required='true'">
			<tr class="rowAttrReq">
			<td>*</td>
			<td><xsl:value-of select="@name"/></td>
			<td><xsl:value-of select="@type"/></td>
			<td><xsl:value-of select="@size"/>&#160;</td>
			<td><xsl:value-of select="@default"/>&#160;</td>
			<td><xsl:value-of select="@javaType"/>&#160;</td>
			</tr>
		</xsl:when>
		<xsl:otherwise>
			<tr class="rowAttrNorm">
			<td>&#160;</td>
			<td><xsl:value-of select="@name"/></td>
			<td><xsl:value-of select="@type"/></td>
			<td><xsl:value-of select="@size"/>&#160;</td>
			<td><xsl:value-of select="@default"/>&#160;</td>
			<td><xsl:value-of select="@javaType"/>&#160;</td>
			</tr>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template match="foreign-key">
	<tr class="rowFKItems">
	<td>
	<b>F=<a href="#{@foreignTable}"><xsl:value-of select="@foreignTable"/></a></b> (<xsl:apply-templates select="reference"/>)
	</td>
	</tr>
</xsl:template>

<xsl:template match="reference">
  &#160;<nobr>L:<xsl:value-of select="@local"/>->F:<xsl:value-of select="@foreign"/></nobr>, 
</xsl:template>

<xsl:template match="index">
	<tr class="rowIXItems">
	<td>
	<b><xsl:value-of select="@name"/></b>(<xsl:apply-templates select="index-column"/>)
	</td>
	</tr>
</xsl:template>

<xsl:template match="index-column">
  &#160;<xsl:value-of select="@name"/>, 
</xsl:template>

</xsl:stylesheet>

