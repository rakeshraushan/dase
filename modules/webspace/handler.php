<?php

class Dase_ModuleHandler_Webspace extends Dase_Handler_Manage {

	public $module_resource_map = array(
		'{collection_ascii_id}/webspace' => 'webspace',
	);

	public function getWebspace($r) 
	{
		$ws_user = $r->get('webspace_name');
		$webspace_url = 'https://webspace.utexas.edu/'.$ws_user.'/DASE/';
		$rss = @simplexml_load_file($webspace_url.'?view=RSS');
		$files = array();
		if ($rss && $rss->channel && $rss->channel->item) {
			foreach ($rss->channel->item as $item) {
				$file['url'] = $item->enclosure['url'];
				$file['length'] = ceil((int) $item->enclosure['length']/1000);
				$file['type'] = $item->enclosure['type'];
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
				$file['name'] = urldecode(str_replace($webspace_url,'',$item->enclosure['url']));
				if ($good_type) {
					$files[] = $file;
				}
			}
		}
		$tpl = new Dase_Template($r);
		$tpl->assign('collection',$this->collection);
		$tpl->assign('files',$files);
		$tpl->assign('webspace_name',$ws_user);
		$r->renderResponse($tpl->fetch(DASE_PATH.'/modules/webspace/templates/index.tpl'));

	}
}
