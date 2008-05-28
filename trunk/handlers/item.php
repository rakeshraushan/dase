<?php

class ItemHandler
{
	public static function asAtom($request)
	{
		$item = Dase_DBO_Item::get($request->get('collection_ascii_id'),$request->get('serial_number'));
		if ($item) {
			$request->renderResponse($item->asAtom(),'application/atom+xml');
		}
		$request->renderError(404);
	}

	public static function asJson($request)
	{
		$item = Dase_DBO_Item::get($request->get('collection_ascii_id'),$request->get('serial_number'));
		if ($item) {
			$request->renderResponse($item->asJson(),'text/plain');
		}
		$request->renderError(404);
	}

	public static function display($request)
	{
		//see if it exists
		if (Dase_DBO_Item::get($request->get('collection_ascii_id'),$request->get('serial_number'))) {
			$t = new Dase_Template($request);
			$feed = Dase_Atom_Feed::retrieve(APP_ROOT.'/atom/collection/'. $request->get('collection_ascii_id') . '/' . $request->get('serial_number'));
			$t->assign('item',$feed);
			$request->renderResponse($t->fetch('item/transform.tpl'));
		} else {
			$request->renderError(404);
		}
	}

	public static function editForm($request)
	{
		//create this
	}
}

