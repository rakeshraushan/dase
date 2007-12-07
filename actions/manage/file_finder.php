<?php 
$REPOS = "/mnt/projects/dase_scanning/mansfield";

$i = 0;
$found = array();

$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($REPOS));
foreach ($dir as $file) {
	if (false === strpos($file->getPathname(),'/.') && $file->isFile()
		) {
			$i++;
			try {
				if ($i < 500) {
					$found[$file->getPathname()] =  Dase_File::getMimeType($file->getPathname());
				}
			} catch(Exception $e) {
			//	print $e->getMessage() . "\n";
			}
		}
}
$result = '';
foreach ($found as $path => $mime) {
	$result .= "<li>$path ($mime)</li>";
}
$result = "<ul>$result</ul>";

$t = new Dase_Xslt(XSLT_PATH.'batch/batch.xsl',XSLT_PATH.'batch/batch.xml');
$t->set('src',$result);
Dase::display($t->transform());

