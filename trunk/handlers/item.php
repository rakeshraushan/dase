<?php

class ItemHandler extends Dase_Handler
{
	public $resource_map = array( 
		'{collection_ascii_id}/{serial_number}' => 'item',
		'{collection_ascii_id}/{serial_number}/notes' => 'notes',
		'{collection_ascii_id}/{serial_number}/notes/{note_id}' => 'note',
	);

	protected function setup($request)
	{
	}	

	public function getItemAtom($request)
	{
		$item = Dase_DBO_Item::get($request->get('collection_ascii_id'),$request->get('serial_number'));
		if ($item) {
			$request->renderResponse($item->asAtom());
		}
		$request->renderError(404);
	}

	public function asJson($request)
	{
		$item = Dase_DBO_Item::get($request->get('collection_ascii_id'),$request->get('serial_number'));
		if ($item) {
			$request->renderResponse($item->asJson(),'text/plain');
		}
		$request->renderError(404);
	}

	public function getItem($request)
	{
		//see if it exists
		if (Dase_DBO_Item::get($request->get('collection_ascii_id'),$request->get('serial_number'))) {
			$t = new Dase_Template($request);
			$feed = Dase_Atom_Feed::retrieve(APP_ROOT.'/item/'. $request->get('collection_ascii_id') . '/' . $request->get('serial_number').'.atom');
			$t->assign('item',$feed);
			$request->renderResponse($t->fetch('item/transform.tpl'));
		} else {
			$request->renderError(404);
		}
	}

	public function editForm($request)
	{
		//create this
	}

	public function deleteNote($request)
	{
		$item = Dase_DBO_Item::get($request->get('collection_ascii_id'),$request->get('serial_number'));
		$user = $request->getUser();
		$note = new Dase_DBO_Content;
		$note->load($request->get('note_id'));
		if ($user->eid == $note->updated_by_eid) {
			$note->delete();
		}
		$item->buildSearchIndex();
		$request->renderResponse('deleted note '.$note->id);
	}

	public function postToNotes($request)
	{
		$fp = fopen("php://input", "rb");
		$bits = NULL;
		while(!feof($fp)) {
			$bits .= fread($fp, 4096);
		}
		fclose($fp);
		$item = Dase_DBO_Item::get($request->get('collection_ascii_id'),$request->get('serial_number'));
		$user = $request->getUser();
		$item->addContent($bits,$user->eid);
		$item->buildSearchIndex();
		$request->renderResponse('added content: '.$bits);
	}

	public function getNotesJson($request)
	{
		$item = Dase_DBO_Item::get($request->get('collection_ascii_id'),$request->get('serial_number'));
		$request->renderResponse($item->getContentsJson());
	}
}

