<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<![if !ie]>
<base href="{$app_root}/modules/friesen/" />
<![endif]>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>The Daily Intelligencer Help Archive</title>

<script type="text/javascript" src="scripts/xstandard.js"></script>

<link rel="stylesheet" type="text/css" href="css/style.css" />

<link rel="stylesheet" type="text/css" href="css/help.css" />

<!-- compliance patch for microsoft browsers -->
<!--[if lt IE 7]>
<script src="../scripts/ie7/ie7-standard-p.js" type="text/javascript">
</script>
<![endif]-->

</head>
<body>

<div class="center">

<div class="brand-daily"><img src="images/brand-daily.gif" width="380" height="31" border="0" alt="The Daily Intelligencer" title="The Daily Intelligencer" /></div>


<div class="container">

<h1><img src="images/track/help.png" alt="" />The Intelligencer Help Archive</h1>

<div class="content-main">

			<div class="search">
			<form class="searchForm" method="get" action="search">
			<input type="text" name="query" size="12" />
			<input type="submit" value="Search" class="button"/>
			<span id="status"></span>
			</form>
			or 
			<form class="searchForm" method="get" id="kwform" action="search">
			<select name="query">
			<option>Select Topic by Keyword</option>
{foreach item=kw key=hash from=$keywords}
<option value="{$kw}">{$kw}</option>
{/foreach}
			</select>
			<input type="submit" value="go"/>
			</form>
			</div>


