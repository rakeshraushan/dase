<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>The Efossils Project</title>
{literal}
<style type="text/css"> img { behavior:	url("scripts/pngbehavior.htc"); }</style>
{/literal}
<link rel="stylesheet" type="text/css" href="{$module_root}/css/splashstyle.css">
</head>
<body><div class="container">
<div class="branding">
<a href="http://www.utexas.edu/its/">Liberal Arts Instructional Technology Services</a> | <a href="http://www.utexas.edu">THE UNIVERSITY OF TEXAS AT AUSTIN</a>
</div>
<div class="header"><a href="http://www.efossils.org"><img src="images/logo.png" alt="eFossils"></a></div>
<div class="timeline">
<div class="sites">
<ul>
{foreach item=s from=$site_array}
<li class="{$s.ascii_id}"><a>{$site.name|upper}</a></li>
{/foreach}
<li class="middleawash"><a>MIDDLE AWASH</a></li>
<li class="hadar"><a>HADAR</a></li>
<li class="olduvai"><a>OLDUVAI</a></li>
<li class="zhoukoudian"><a>ZHOUKOUDIAN</a></li>
<li class="atapuerca"><a>ATAPUERCA</a></li>
</ul>
<div class="scale"><div class="years"><ul>
<li class="first"><a>0ma</a></li>
<li class="middle"><a>1ma</a></li>
<li class="middle"><a>2ma</a></li>
<li class="middle"><a>3ma</a></li>
<li class="middle"><a>4ma</a></li>
<li class="middle"><a>5ma</a></li>
<li class="last"><a>6ma</a></li>
</ul></div></div>
</div>
<div class="bar"></div>
<div class="mainsiteattributes"><div class="Attributes"><ul>
<li class="Introduction"><a href="Introduction">INTRODUCTION</a></li>
<li class="Hominins"><a href="Hominins">HOMININS</a></li>
<li class="Archaeology"><a href="Archaeology">ARCHAEOLOGY</a></li>
<li class="Fauna"><a href="Fauna">FAUNA</a></li>
<li class="Geology"><a href="Geology">GEOLOGY</a></li>
<li class="Paleoecology"><a href="Paleoecology">PALEOECOLOGY</a></li>
</ul></div></div>
</div>
<div class="primaryouter"><div class="primary">
<div class="mainsite">
<h2>{$site.name}</h2>
<div class="feature"><img src="images/zhoukoudianskull.jpg"></div>
<div class="info">
<img src="images/zmap.jpg"><h2>{$site.name}</h2>
<p>
{$site.text.basic}
</p>
<h3><a href="intermediate">Intermediate Reading</a></h3>
<h4> | </h4>
<h4><a href="advanced">Advanced Reading</a></h4>
</div>
<div class="spacer"></div>
</div>
<div class="spacer"></div>
</div></div>
<div class="foot"><ul>
<li><a>FIRST TIME USERS</a></li>
<li><a>INFORMATION</a></li>
<li><a href="http://www.eskeletons.org/project_info.cfm">THE PROJECTS</a></li>
<li><a>RESOURCES</a></li>
<li class="lastone"><a href="http://www.eskeletons.org">eSKELETONS</a></li>
</ul></div>
</div></body>
</html>
