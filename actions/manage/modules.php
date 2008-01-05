<?php
$t = new Dase_Xslt(
	XSLT_PATH.'manage/modules.xsl',
	XSLT_PATH.'manage/source.xml'
);

$sx = new SimpleXMLElement('<modules/>');

$dir = (DASE_PATH . "/modules");
foreach (new DirectoryIterator($dir) as $file) {
	if ($file->isDir() && !$file->isDot()) {
		$module = $file->getFilename();
		$mod = $sx->addChild('module',$module);
		$mod->addAttribute('installed','installed');
	}
}

$t->addSourceNode($sx);
Dase::display($t->transform());
