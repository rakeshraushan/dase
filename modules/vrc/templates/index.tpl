<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<base href="{$app_root}/modules/vrc/"/>
<title>Fine Arts VRC</title>
<link rel="stylesheet" type="text/css" href="{$module_root}/css/style.css">
</head>
<body>
<div id="container">
<h1>Fine Arts VRC Image and Metadata Management <span>(beta)</span></h1>

{if $msg}
<h2 class="alert">{$msg}</h2>
{/if}

<form id="criteria" action="list" method="get">
select records modified within last
<select name="days">
<option {if $days == 5}selected="selected"{/if}>5</option>
<option {if $days == 10}selected="selected"{/if}>10</option>
<option {if $days == 15}selected="selected"{/if}>15</option>
<option {if $days == 20}selected="selected"{/if}>20</option>
<option {if $days == 25}selected="selected"{/if}>25</option>
<option {if $days == 30}selected="selected"{/if}>30</option>
<option {if $days == 40}selected="selected"{/if}>40</option>
<option {if $days == 50}selected="selected"{/if}>50</option>
<option {if $days == 60}selected="selected"{/if}>60</option>
<option {if $days == 80}selected="selected"{/if}>80</option>
<option {if $days == 100}selected="selected"{/if}>100</option>
<option {if $days == 120}selected="selected"{/if}>120</option>
<option {if $days == 200}selected="selected"{/if}>200</option>
<option {if $days == 400}selected="selected"{/if}>400</option>
<option {if $days == 1000}selected="selected"{/if}>1000</option>
<option {if $days == 'none'}selected="selected"{/if} value="none">no modified date</option>
</select>
days
with image filename beginning
<input type="text" size="8" name="q" value="{$q}"/>
<input type="submit" value="retrieve"/>
</form>

<h3>{$items|@count} items returned</h3>
{foreach item=item from=$items}
<ul>{$item}</ul>
{/foreach}

<div class="spacer"></div>
</div>
<div id="footer">
a <a href="http://dase.laits.utexas.edu">DASe</a> project
</div>
</body>
</html>
