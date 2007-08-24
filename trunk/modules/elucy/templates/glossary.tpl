<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<base href="{$app_root}/modules/elucy/"/>
<title>eLucy Glossary</title>
<!-- the following allows the transparent png logo to display properly in IE -->
{literal}
<style type="text/css"> img { behavior:	url("pngbehavior.htc"); }</style>
{/literal}

<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="css/style.css">
<script type="text/javascript" src="scripts/jquery.js"></script> 
<script type="text/javascript" src="scripts/elucy_jquery.js"></script>
</head>
<body>

<div class="container">

<div class="branding">
<a href="http://www.laits.utexas.edu/its/"><b>Liberal Arts</b><img src="images/its.bmp"/> Instructional Technology Services</a> | <a href="credits.html">LAITS Production Credits</a> | <a   href="www.utexas.edu"><img class="ut" src="images/utlogo.bmp"/></a> 

</div> <!-- close branding -->

<div class="header">

<a href=""><img src="images/logoLucy.png" alt="eFossils"/></a>
</div> <!-- close header -->
<div class="primaryouter">
<div class="primarylucy">
<div class="mainsite">

<div class="topbarp">
<h2>Glossary</h2></div> <!-- close topbars -->
<div class="bottombarp">			
</div> <!-- close bottombars -->

<div class="glossary_container">

<div class="letters">
<a href="glossary#A">A</a> | 
<a href="glossary#B">B</a> | 
<a href="glossary#C">C</a> | 
<a href="glossary#D">D</a> | 
<a href="glossary#E">E</a> | 
<a href="glossary#F">F</a> | 
<a href="glossary#G">G</a> | 
<a href="glossary#H">H</a> | 
<a href="glossary#I">I</a> | 
<a href="glossary#J">J</a> | 
<a href="glossary#K">K</a> | 
<a href="glossary#L">L</a> | 
<a href="glossary#M">M</a> | 
<a href="glossary#N">N</a> | 
<a href="glossary#O">O</a> | 
<a href="glossary#P">P</a> | 
<a href="glossary#Q">Q</a> | 
<a href="glossary#R">R</a> | 
<a href="glossary#S">S</a> | 
<a href="glossary#T">T</a> | 
<a href="glossary#U">U</a> | 
<a href="glossary#V">V</a> | 
<a href="glossary#W">W</a> | 
<a href="glossary#X">X</a> | 
<a href="glossary#Y">Y</a> | 
<a href="glossary#Z">Z</a>
</div>

<dl class="glossary">
{foreach key=term item=def from=$definitions}
<a name="{$term|substr:0:1|strtoupper}">&nbsp;</a>
<dt>{$term}</dt>
<dd>{$def}</dd>
{/foreach}
</dl>

</div>

</div> <!-- close mainsite -->
<div class="spacer"></div>
<!-- <a href="viewer.html" onClick="return popup(this, 'notes')">Bone Viewer</a> bone viewer -->
</div> <!-- close primary -->
</div> <!-- close primaryouter -->

<div class="foot">
<ul>
<li><a href="ftu/index.html">FIRST TIME USERS</a></li>
<li><a href="www.efossils.org">eFOSSILS</a></li>
<li><a href="tp/index.html">THE PROJECTS</a></li>
<li><a href="resources/index.html">RESOURCES</a></li>
<li class="lastone"><a href="http://www.eskeletons.org">eSKELETONS</a></li>
</ul>
</div> <!--close foot-->
</div> <!--close container-->
</body>
</html>
