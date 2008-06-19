<?php

class Dase_Handler_Item extends Dase_Handler
{
	public $resource_map = array( 
		'{collection_ascii_id}/{serial_number}' => 'item',
		'{collection_ascii_id}/{serial_number}/edit' => 'edit_form',
		'{collection_ascii_id}/{serial_number}/notes' => 'notes',
		'{collection_ascii_id}/{serial_number}/notes/{note_id}' => 'note',
	);

	protected function setup($request)
	{
		$this->item = Dase_DBO_Item::get($request->get('collection_ascii_id'),$request->get('serial_number'));
		if (!$this->item) {
			$request->renderError(404);
		}
		$this->user = $request->getUser();
		if (!$this->user->can('read','item',$this->item)) {
			$request->renderError(401);
		}
	}	

	public function getItemAtom($request)
	{
		$request->renderResponse($this->item->asAtom());
	}

	public function asJson($request)
	{
		$request->renderResponse($this->item->asJson(),'text/plain');
	}

	public function getItem($request)
	{
		//a bit inefficient since the setup item get is unecessary, assuming atom feed error reporting
		$t = new Dase_Template($request);
		$feed = Dase_Atom_Feed::retrieve(APP_ROOT.'/item/'. $request->get('collection_ascii_id') . '/' . $request->get('serial_number').'.atom');
		$t->assign('item',$feed);
		$request->renderResponse($t->fetch('item/transform.tpl'));
	}

	public function getEditFormJson($request)
	{
		$request->renderResponse($this->item->getEditFormJson());
	}

	public function deleteNote($request)
	{
		$note = new Dase_DBO_Content;
		$note->load($request->get('note_id'));
		if ($this->user->eid == $note->updated_by_eid) {
			$note->delete();
		}
		$this->item->buildSearchIndex();
		$request->renderResponse('deleted note '.$note->id);
	}

	public function postToNotes($request)
	{
		//auth: anyone can post to an item they can read
		$fp = fopen("php://input", "rb");
		$bits = NULL;
		while(!feof($fp)) {
			$bits .= fread($fp, 4096);
		}
		fclose($fp);
		$this->item->addContent($bits,$this->user->eid);
		$this->item->buildSearchIndex();
		$request->renderResponse('added content: '.$bits);
	}

	public function getNotesJson($request)
	{
		$request->renderResponse($this->item->getContentsJson());
	}
}

