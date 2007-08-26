<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<base href="{$app_root}/modules/elucy/"/>
<title>eLucy</title>
<!-- the following allows the transparent png logo to display properly in IE -->
{literal}
<style type="text/css"> img { behavior:	url("pngbehavior.htc"); }</style>
{/literal}

<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="css/style.css">
<!--
<link rel="stylesheet" type="text/css" href="http://dev.laits.utexas.edu/efossils/elucy2/splashstyle.css">
-->
	<script type="text/javascript" src="scripts/jquery.js"></script> 
 		<script type="text/javascript" src="scripts/elucy_jquery.js"></script>


</head>
<body>

<div class="container">

	<div class="branding">
				<a href="http://www.laits.utexas.edu/its/"><b>Liberal Arts</b><img src="images/its.bmp"/> Instructional Technology Services</a> | <a href="credits.html">LAITS Production Credits</a> | <a   href="http://www.utexas.edu"><img class="ut" src="images/utlogo.bmp"/></a> 

			</div> <!-- close branding -->

<div class="header">
		
	<a href=""><img src="images/logoLucy.png" alt="eFossils"/></a>
</div> <!-- close header -->






<div class="primaryouter">
<div class="primary">

<div class="mainsite">


	<div class="feature">
		
		<h2>meet</h2>  <h4>Lucy</h4>
		<div class="spacer"></div>
		
		<h3>The eLucy site is coming soon</h3>
		<p>eLucy is part of the eFossils project. All eLucy activities can be found on eFossils and through the colored side tabs.</p>
		<br><br>
		<p><font size="5">L</font>ucy is a 3.2 million year old fossil hominid, a close relative of humans. Her species, Australopithecus afarensis, is now extinct. Lucy's official museum number is AL 288-1.  She was found in 1974 by a team of French and American anthropologists at the Hadar Site in Ethiopia. While today this site is a desert, three million years ago it was a large lake surrounded by trees. 40% of Lucy's skeleton was recovered, an astonishing feat for a fossil so old.The bones of recovered of Lucy include pieces of the skull, lower jaw, ribs, vertebrae, arm and leg bones, and hip bones. We know Lucy is a female because the shape of her hip bones resembles that of modern human females.</p> 
		<img src="images/lucy2.crushed.png">
	


	</div> <!-- close feature -->

	
	
		
		<a href="teacher"><div class="activities">

			<div class="teacher">
				<h2>TEACHERS > Lessons</h2>
			</div> <!-- close teacher -->

						
			<p>{$teacher_text}</p>
	
		</div></a>
		
		<a href="student"><div class="activities2">

			<div class="student">
			<h2>STUDENTS > Activities</h2>

			</div> <!-- close student -->

			<p>{$student_text}</p>
			
		</div></a>

		
		<a href="comparison"><div class="activities3">
			<div class="comparison">
				<h2>COMPARISON PHOTOS</h2>

			</div> <!-- close comparison -->
			
			<p>{$comp_text}</p>

		</div></a>





</div> <!-- close mainsite -->

<div class="spacer"></div>
<!-- <a href="viewer.html" onClick="return popup(this, 'notes')">Bone Viewer</a> bone viewer -->



</div> <!-- close primary -->
</div> <!-- close primaryouter -->

{include file="footer.tpl"}

</div> <!--close container-->



</body>
</html>

