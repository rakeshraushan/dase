<?php


$mimes = array('image','video','audio');

$directory = getcwd();
$it = new DirectoryIterator($directory);
$feed_updated = 0;
foreach ($it as $file) {
	if ($file->isFile()) {
		$filename = $file->getPathname();
		$output = array();
		exec("file -i -b $filename",$output);
		foreach($mimes as $mime) {
			if (false !== strpos($output[0],'image')) {
				$valid_media = 1;	
			};
		}
		if ($valid_media) {
			$updated = $file->getMTime();
			if ($updated > $feed_updated) {
				$feed_updated = $updated;
			}
			$author = $file->getOwner();
			$size = $file->getSize();
			print "$type $size / $filename / $updated / $author\n";
		}
	}
} 

function newestFileTS($dir) {
	$it = new DirectoryIterator($directory);
	$feed_updated = 0;
	foreach ($it as $file) {
		if ($file->isFile()) {
			$filename = $file->getPathname();
			$output = array();
			exec("file -i -b $filename",$output);
			foreach($mimes as $mime) {
				if (false !== strpos($output[0],'image')) {
					$valid_media = 1;	
				};
			}
			if ($valid_media) {
				$updated = $file->getMTime();
				if ($updated > $feed_updated) {
					$feed_updated = $updated;
				}
			}
		}
	} 
	return $feed_updated;
}
exit;


$writer = new XMLWriter();
$writer->openMemory();
$writer->setIndent(true);
$writer->startDocument('1.0','UTF-8');
$writer->startElement('feed');
$writer->writeAttribute('xmlns','http://www.w3.org/2005/Atom');
$writer->startElement('title');
$writer->text($directory);
$writer->endElement();
$writer->startElement('author');
$writer->startElement('name');
$writer->text('john smith');
$writer->endElement();
$writer->endElement();
$writer->startElement('updated');
$writer->text(newestFileTS($directory));
$writer->endElement();
$item = new Dase_DB_Item;
$item->collection_id = $this->id;
$item->setLimit(10);
foreach($item->findAll() as $it) {
	$writer->startElement('entry');
	$writer->writeAttribute('xml:base', APP_HTTP_ROOT . "/{$this->ascii_id}/");
	$writer->startElement('id');
	$writer->text(APP_ROOT . "/{$this->ascii_id}/{$it['serial_number']}");
	$writer->endElement();
	$writer->startElement('updated');
	$writer->text(date('c',$it['last_update']));
	$writer->endElement();
	$writer->writeAttribute('xml:base', APP_HTTP_ROOT . "/{$this->ascii_id}/");
	$item_type = Dase_DB_Object::getArray('item_type',$it['item_type_id']);
	if (isset($item_type['ascii_id'])) {
		$writer->startElement('category');
		$writer->writeAttribute('scheme',APP_HTTP_ROOT . "/{$this->ascii_id}/item_type");
		$writer->writeAttribute('term',$item_type['ascii_id']);
		$writer->writeAttribute('label',$item_type['name']);
		$writer->endElement();
	}
	$writer->startElement('link');
	if (!$mf['file_size']) {
		$h = get_headers(APP_ROOT . "/{$this->ascii_id}/media/{$mf['size']}/{$mf['filename']}",1);
		$mf['file_size'] = $h['Content-Length'];
	}
	$writer->writeAttribute('length',$mf['file_size']);
	$writer->writeAttribute('type',$mf['mime_type']);
	$writer->writeAttribute('rel',APP_ROOT . "/media/{$mf['size']}");
	$writer->writeAttribute('href',"/media/{$mf['size']}/{$mf['filename']}");
	$writer->endElement();
}
$writer->startElement('content');
$writer->writeAttribute('src', APP_ROOT . "/{$this->ascii_id}/media/thumbnail/$thumbnail_file");
$writer->writeAttribute('type', $thumbnail_type);
$writer->endElement();
$writer->startElement('summary');
$writer->text('thumbnail image');
$writer->endElement();
$writer->endElement();
}
$writer->endElement();
$writer->endDocument();
return $writer->flush(true);
