<?
	$_GET['xml'] or $_GET['xml'] = 'sample_form.xml';
	$_GET['xsl'] or $_GET['xsl'] = 'default/main.xsl';


	$xml_dom = new domDocument();
	$xml_dom->load($_GET['xml']);
	
	$xsl_dom = new domDocument();
	$xsl_dom->load($_GET['xsl']);
	
	$proc = new xsltprocessor;
	$proc->importStylesheet($xsl_dom);
	$html = $proc->transformToXML($xml_dom);
	
	echo $html;
				
?>