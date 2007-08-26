<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<base href="{$app_root}/modules/elucy/"/>
<title>eLucy {$page_title}</title>
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

<div class="topbar{$page_name|substr:0:1}">
<h2>{$page_title}</h2></div> <!-- close topbars -->
<div class="bottombar{$page_name|substr:0:1}">			
</div> <!-- close bottombars -->

<div class="list1">
<h3><a href="">Puzzle</a></h3>
<div><a href="">puzzle1</a><br>
<a href="">puzzle2</a></div>

<h3><a href="">lessons</a></h3>
<div><a href="">lesson1</a>
<br>
<a href="">lesson2</a>	</div>
</div> <!-- close list -->
</div> <!-- close mainsite -->
<div class="spacer"></div>
<!-- <a href="viewer.html" onClick="return popup(this, 'notes')">Bone Viewer</a> bone viewer -->
</div> <!-- close primary -->
</div> <!-- close primaryouter -->

{include file="footer.tpl"}

</div> <!--close container-->
</body>
</html>
