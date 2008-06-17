<?php

class AttributeHandler extends Dase_Handler
{
	public $attribute;
	public $collection;
	public $resource_map = array(
		'{collection_ascii_id}' => 'attributes',
		'{collection_ascii_id}/{att_ascii_id}' => 'attribute',
	);

	public function setup($request)
	{
		if ($request->has('collection_ascii_id')) {
			$this->collection = Dase_DBO_Collection::get($request->get('collection_ascii_id'));
		}
		if ($request->has('att_ascii_id')) {
			$this->attribute = Dase_DBO_Attribute::get($request->get('collection_ascii_id'),$request->get('att_ascii_id'));
		}
	}

	public function getAttributesAtom($request) 
	{
		$atts = new Dase_DBO_Attribute;
		$feed = new Dase_Atom_Feed;
		foreach ($atts->find() as $att) {
			$att->injectAtomEntryData($feed->addEntry(),$att->getCollection());
		}
		$request->renderResponse($feed->asXml());
	}

	public function getAttributeJson($request) 
	{
		$att = new Dase_DBO_Attribute;
		$att->ascii_id = $request->get('att_ascii_id');
		$att->findOne();
		$request->renderResponse($att->asJson());
	}
}

