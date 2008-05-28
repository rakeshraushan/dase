<?php

class AttributeHandler
{
	public $attribute;
	public $collection;
	public $resource_map = array(
		'{collection_ascii_id}' => 'attributes',
		'{collection_ascii_id}/{att_ascii_id}' => 'attribute',
	);

	public function setup($r)
	{
		if ($r->has('collection_ascii_id')) {
			$this->collection = Dase_DBO_Collection::get($r->get('collection_ascii_id'));
		}
		if ($r->has('att_ascii_id')) {
			$this->attribute = Dase_DBO_Attribute::get($r->get('collection_ascii_id'),$r->get('att_ascii_id'));
		}
	}

	public function attributeListAsAtom($request) 
	{
		$atts = new Dase_DBO_Attribute;
		$feed = new Dase_Atom_Feed;
		foreach ($atts->find() as $att) {
			$att->injectAtomEntryData($feed->addEntry(),$att->getCollection());
		}
		$request->renderResponse($feed->asXml(),'application/atom+xml');
	}
}

