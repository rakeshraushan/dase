<?php
class Dase_Atom_Feed_Collection extends Dase_Atom_Feed 
{
	function __construct($dom = null)
	{
		parent::__construct($dom);
	}

	function __get($var) {
		//allows smarty to invoke function as if getter
		$classname = get_class($this);
		$method = 'get'.ucfirst($var);
		if (method_exists($classname,$method)) {
			return $this->{$method}();
		} else {
			return parent::__get($var);
		}
	}

	function getDescription()
	{
		return $this->getSubtitle();
	}

	function getItemCount()
	{
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
			if ('http://daseproject.org/category/collection/item_count' == $el->getAttribute('scheme')) {
				return $el->getAttribute('term');
			}
		}
	}

	function ingest($request,$fetch_enclosures=false) 
	{
		$user = $request->getUser();
		$coll_ascii_id = $this->getAsciiId();
		$count = $this->getItemCount();
		$collection_name = $this->getTitle();
		$ascii_id = $this->getAsciiId();
		$c = new Dase_DBO_Collection;
		$c->collection_name = $collection_name;
		if (Dase_DBO_Collection::get($ascii_id) || $c->findOne()) {
			//$request->renderError(409,'collection already exists');
			Dase_Log::info('collection exists '.$c->collection_name);
			return;
		}
		$c->ascii_id = $ascii_id;
		$c->is_public = 0;
		$c->created = date(DATE_ATOM);
		$c->updated = date(DATE_ATOM);
		if ($c->insert()) {
			Dase_Log::info('created collection '.$c->collection_name);
			$media_dir =  Dase_Config::get('path_to_media').'/'.$ascii_id;
			if (file_exists($media_dir)) {
				//$request->renderError(409,'collection media archive exists');
				Dase_Log::info('collection media archive exists');
			} else {
				if (mkdir("$media_dir")) {
					chmod("$media_dir",0775);
					foreach (Dase_Config::get('sizes') as $size => $access_level) {
						mkdir("$media_dir/$size");
						Dase_Log::info('created directory '.$media_dir.'/'.$size);
						chmod("$media_dir/$size",0775);
					}
					//todo: compat only!
					symlink($media_dir,$media_dir.'_collection');
				}
			}
			foreach ($this->getEntries() as $entry) {
				if ('item' == $entry->getEntryType()) {
					$request->set('collection_ascii_id',$c->ascii_id);
					$entry->insert($request,$fetch_enclosures);
				}
			}
		}
	}
}
