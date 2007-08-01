<?php

class Dase_Image 
{

	private $image_file;

	public function __construct($file) {
		if (is_file($file)) {
			$this->image_file = $file;
		}
	}

	public function makeImage($geometry='100x100',$outfile = '') {
		if (!$outfile) {
			$outfile = $this->image_file . '_thumb.jpg';
		}
		//note: mogrify changes file in place, convert writes new file
		$results = exec("/usr/bin/convert -format jpeg -resize '$geometry >' -colorspace RGB $this->image_file $outfile");
	}

	public static function makeDirThumbnails($directory,$max_side = 100) {
		mkdir($directory . '/thumbs',0777);
		foreach (new DirectoryIterator($directory) as $file) {
			if ($file->isFile()) {
				$path = $file->getPathname();
				$img = new Dase_Image($path);
				$info = pathinfo($path);
				$out = $info['dirname'] . '/thumbs/' . basename($info['basename'],"." . $info['extension']) . '_'  . $max_side . '.jpg';
				$geometry = $max_side . 'x' . $max_side;
				$img->makeImage($geometry,$out);
			}
		}
	}
}

