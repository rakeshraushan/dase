<?php

$IMAGE_REPOS = "/mnt/dar/favrc/for-dase";
if (!file_exists($IMAGE_REPOS)) {
	die ("cannot find $IMAGE_REPOS");
}
$found = array();

$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($IMAGE_REPOS));

$hash = "<?php\n\n";

foreach ($dir as $file) {
	if (!strpos($file,'/.')) {
		if (strpos($file,'.jpg') || strpos($file,'.tif')) {
			$k = basename($file);
			$hash .= "\$images['$k'] = \"$file\";\n";
		}
	}
}

file_put_contents('images.php',$hash);

echo "complete!";
