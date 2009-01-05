<?php

class Dase_Atom_Entry_Collection extends Dase_Atom_Entry
{
	function __construct($dom=null,$root=null)
	{
		parent::__construct($dom,$root);
	}

	function create($request)
	{
		$atom_author = $this->getAuthorName();
		$user = $request->getUser('http');
		if ($atom_author != $user->eid) {
			$request->renderError(401,'users do not match');
		}

		$collection_name = $this->getName();
		$ascii_id = $this->getAsciiId();
		$c = new Dase_DBO_Collection;
		$c->collection_name = $collection_name;
		if (Dase_DBO_Collection::get($ascii_id) || $c->findOne()) {
			$request->renderError(409,'collection already exists');
		}
		$c->ascii_id = $ascii_id;
		$media_dir =  Dase_Config::get('path_to_media').'/'.$ascii_id;
		if (file_exists($media_dir)) {
			$request->renderError(409,'collection media archive exists');
		}
		$c->is_public = 0;
		$c->created = date(DATE_ATOM);
		$c->updated = date(DATE_ATOM);
		if ($c->insert()) {
			Dase_Log::info('created collection '.$c->collection_name);
			if (mkdir("$media_dir")) {
				chmod("$media_dir",0775);
				foreach (Dase_Config::get('sizes') as $size => $access_level) {
					mkdir("$media_dir/$size");
					Dase_Log::info('created directory '.$media_dir.'/'.$size);
					chmod("$media_dir/$size",0775);
				}
				symlink($media_dir,$media_dir.'_collection');
			}
			foreach (array('title','description','keyword','rights') as $att) {
				$a = new Dase_DBO_Attribute;
				$a->ascii_id = $att;
				$a->attribute_name = ucfirst($att);
				$a->collection_id = $c->id;
				$a->in_basic_search = true;
				$a->is_on_list_display = true;
				$a->is_public = true;
				$a->html_input_type = 'text';
				if ('description' == $att) {
					$a->html_input_type = 'textarea';
				}
				$a->updated = date(DATE_ATOM);
				$a->insert();
			}
			$cm = new Dase_DBO_CollectionManager;
			$cm->collection_ascii_id = $ascii_id;
			$cm->dase_user_eid = $atom_author; //checked above to be same as current user
			$cm->auth_level = 'superuser';
			$cm->created = date(DATE_ATOM);
			$cm->created_by_eid = $user->eid;
			if ($cm->insert()) {
				Dase_Log::info('created admin user '.$ascii_id.'::'.$atom_author);
			} else {
				Dase_Log::info('could not create admin user');
			}
			$user->expireDataCache();
			return $ascii_id;
		} else {
			return false;
		}
	}

	function getItemCount()
	{
		foreach ($this->root->getElementsByTagNameNS(Dase_Atom::$ns['atom'],'category') as $el) {
			if ('http://daseproject.org/category/collection/item_count' == $el->getAttribute('scheme')) {
				return $el->getAttribute('term');
			}
		}
	}

	function getName()
	{
		return $this->getTitle();
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
}
