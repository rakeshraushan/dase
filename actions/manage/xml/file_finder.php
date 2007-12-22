<?php 
$REPOS = "/mnt/projects/dase_scanning/mansfield";

$sx = new SimpleXMLElement("<files/>");
$i = 0;
$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($REPOS));
foreach ($dir as $file) {
	if (false === strpos($file->getPathname(),'/.') && $file->isFile()
		) {
			$i++;
			try {
				if ($i < 500) {
					$f = $sx->addChild('file',$file->getPathname());
				   	$f->addAttribute('mime_type',Dase_File::getMimeType($file->getPathname()));
				   	$f->addAttribute('last_modified',Dase_File::getMTime($file->getPathname()));
				}
			} catch(Exception $e) {
			//	print $e->getMessage() . "\n";
			}
		}
}
Dase::display($sx->asXml(),true,'application/xml');

