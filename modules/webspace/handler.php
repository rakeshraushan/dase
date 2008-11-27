<?php

class Dase_ModuleHandler_Webspace extends Dase_Handler_Manage {

	public $module_resource_map = array(
		'{collection_ascii_id}/webspace' => 'webspace',
	);

	public function getWebspace($r) 
	{
		$ws_path = $r->get('webspace_path');
		$webspace_url = 'https://webspace.utexas.edu/'.$ws_path;
		$rss_data = @file_get_contents($webspace_url.'?view=RSS');
		$files = array();
		$paths = array();
		$invalid_files = array();
		if ($rss_data) {
			$rss = new DOMDocument;
			$rss->loadXML($rss_data);
			$good_type = '';
			foreach ($rss->getElementsByTagName('item') as $item) {
				$enc = $item->getElementsByTagName('enclosure')->item(0);
				if ($enc) {
					$file['url'] = $enc->getAttribute('url');
					$file['length'] = ceil((int) $enc->getAttribute('length')/1000);
					$file['type'] = $enc->getAttribute('type');
					$types = Dase_Config::get('media_types');
					$good_type = '';
					$content_type = $file['type'];
					if (false !== strpos($content_type,'/')) {
						list($type,$subtype) = explode('/',$content_type);
						list($subtype) = explode(";",$subtype); // strip MIME parameters
						foreach($types as $t) {
							list($acceptedType,$acceptedSubtype) = explode('/',$t);
							if($acceptedType == '*' || $acceptedType == $type) {
								if($acceptedSubtype == '*' || $acceptedSubtype == $subtype)
									$good_type = $type . "/" . $subtype;
							}
						}
					}
					$file['name'] = trim(urldecode(str_replace($webspace_url,'',$file['url'])),'/');
				}
				if ($good_type) {
					$files[] = $file;
				} else {
					if ($enc) {
						$invalid_files[] = $file;
					} else {
						$path_href = $item->getElementsByTagName('link')->item(0)->firstChild->substringData(0,200);
						$path['path_rel'] = str_replace('https://webspace.utexas.edu/','',$path_href);
						$path['path_name'] = $item->getElementsByTagName('title')->item(0)->firstChild->substringData(0,200);
						$paths[] = $path;
					}
				}
			}
		}
		$tpl = new Dase_Template($r);
		$tpl->assign('collection',$this->collection);
		$tpl->assign('files',$files);
		$tpl->assign('invalid_files',$invalid_files);
		$tpl->assign('paths',$paths);
		$tpl->assign('webspace_path',$ws_path);
		$r->renderResponse($tpl->fetch(DASE_PATH.'/modules/webspace/templates/index.tpl'));
	}
}
