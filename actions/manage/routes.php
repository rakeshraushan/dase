<?php
$t = new Dase_Xslt(
	XSLT_PATH.'manage/routes.xsl',
	XSLT_PATH.'manage/source.xml'
);

$routes = Dase::compileRoutes();
$sx = new SimpleXMLElement('<routes/>');
foreach ($routes as $http_method => $routes_set) {
	$method = $sx->addChild('h3',$http_method . ' method');
	$dl = $sx->addChild('dl');
	$dl->addAttribute('class','routes');
	foreach($routes_set as $match => $atts) {
		$dl->addChild('dt',$match);
		if (is_array($atts)){
			foreach($atts as $name => $value) {
				$dl->addChild('dd',"[$name] $value");
			}
		}
	}
}
$t->addSourceNode($sx);
Dase::display($t->transform());
