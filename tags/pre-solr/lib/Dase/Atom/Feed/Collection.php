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

	function getName() 
	{
		return $this->getTitle();
	}

	function ingest($db,$r,$fetch_enclosures=false) 
	{
		$user = $r->getUser();
		$coll_ascii_id = $this->getAsciiId();
		$count = $this->getItemCount();
		$collection_name = $this->getTitle();
		$ascii_id = $this->getAsciiId();
		$c = new Dase_DBO_Collection($db);
		$c->collection_name = $collection_name;
		if (Dase_DBO_Collection::get($db,$ascii_id) || $c->findOne()) {
			//$r->renderError(409,'collection already exists');
			$r->logger()->info('collection exists '.$c->collection_name);
			return;
		}
		$c->ascii_id = $ascii_id;
		$c->is_public = 0;
		$c->created = date(DATE_ATOM);
		$c->updated = date(DATE_ATOM);
		if ($c->insert()) {
			$r->logger()->info('created collection '.$c->collection_name);
			$media_dir =  $r->retrieve('config')->getMediaDir().'/'.$ascii_id;
			if (file_exists($media_dir)) {
				//$r->renderError(409,'collection media archive exists');
				$r->logger()->info('collection media archive exists');
			} else {
				if (mkdir("$media_dir")) {
					chmod("$media_dir",0775);
					foreach (Dase_Acl::$sizes as $size => $access_level) {
						mkdir("$media_dir/$size");
						$r->logger()->info('created directory '.$media_dir.'/'.$size);
						chmod("$media_dir/$size",0775);
					}
					//todo: compat only!
					symlink($media_dir,$media_dir.'_collection');
				}
			}
			foreach ($this->getEntries() as $entry) {
				if ('item' == $entry->getEntryType()) {
					$r->set('collection_ascii_id',$c->ascii_id);
					$entry->insert($db,$r,$fetch_enclosures);
				}
			}
		}
	}
}
