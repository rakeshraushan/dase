<?php

class Dase_Handler_Media extends Dase_Handler
{
	public $resource_map = array(
		'{collection_ascii_id}' => 'collection',
		'{collection_ascii_id}/{size}/{serial_number}' => 'media_file',
	);

	protected function setup($r)
	{
		//finish!!!!!!!!!!!!!!!!!!!!!!!!!!
		$this->collection_ascii_id = $r->get('collection_ascii_id');
		$this->serial_number = $r->get('serial_number');
		$this->size = $r->get('size');
		/*
		if (!Dase_Acl::check($this->collection_ascii_id,$this->size)) {
			if (!$path) {
				$user = $r->getUser();
				if (!$user) {
					$r->renderError(401,'cannot access media');
				}
				if (!Dase_Acl::check($this->collection_ascii_id,$this->size,$user->eid)) {
					$r->renderError(401,'cannot access media');
				}
			}
			//get coll path to media!!!!!!!!
		}
		 */
	}

	public function getMediaFileJpg($r)
	{
		$r->serveFile($this->_getFilePath($this->collection_ascii_id,$this->serial_number,$this->size,$r->format),$r->response_mime_type);
	}

	/** AtomPub Media Link Entry */
	public function getMediaFileAtom($r)
	{
		$collection_ascii_id = $r->get('collection_ascii_id');
		$serial_number = $r->get('serial_number');
		$size = $r->get('size');
		$m = new Dase_DBO_MediaFile;
		$m->p_collection_ascii_id = $collection_ascii_id;
		$m->p_serial_number = $serial_number;
		$m->size = $size; //meaning media directory
		if ($m->findOne()) {
			$mle_url = APP_ROOT .'/media/'.$m->p_collection_ascii_id.'/'.$m->size.'/'.$m->p_serial_number.'.atom';
			header("Location:". $mle_url,TRUE,201);
			$r->response_mime_type = 'application/atom+xml';
			$r->renderResponse($m->asAtom());
		}
	}

	private function _getFilePath($collection_ascii_id,$serial_number,$size,$format)
	{
		$path = Dase_Config::get('path_to_media').'/'.
			$collection_ascii_id.'/'.
			$size.'/'.
			$serial_number.'.'.$format;
		return $path;
	}

	public function postToCollection($r)
	{
		$user = $r->getUser('http');
		$c = Dase_DBO_Collection::get($r->get('collection_ascii_id'));
		if (!$user->can('write',$c)) {
			$r->renderError(401,'cannot post media to this item');
		}
		//hand off to item handler
		$item_handler = new Dase_Handler_Item;
		$item_handler->item = $c->createNewItem(null,$user->eid);
		$item_handler->postToMedia($r);
		//if something goes wrong and control returns here
		$r->renderError(500,'error in post to collection');
	}
}

