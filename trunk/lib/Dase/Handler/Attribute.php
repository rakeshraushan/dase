<?php

class Dase_Handler_Attribute extends Dase_Handler
{
	public $attribute;
	public $collection;
	public $resource_map = array(
		'{collection_ascii_id}' => 'attributes',
		'{collection_ascii_id}/{att_ascii_id}' => 'attribute',
		'{collection_ascii_id}/{att_ascii_id}/values' => 'attribute_values',
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
		$request->renderResponse($this->attribute->asJson());
	}

	/** implicit 1000 limit */
	public function getAttributeValuesJson($request) 
	{
		$attr = Dase_DBO_Attribute::get($request->get('collection_ascii_id'),$request->get('att_ascii_id'));
		if (0 == $attr->collection_id) {
			//since it is admin att we need to be able to limit to items in this coll
			$values_array = $attr->getDisplayValues($this->collection->ascii_id);
		} else {
			$values_array = $attr->getDisplayValues();
		}
		$result['att_name'] = $attr->attribute_name;
		$result['att_ascii'] = $attr->ascii_id;
		$result['coll'] = $request->get('collection_ascii_id');
		$result['values'] = $values_array;
		$request->renderResponse(Dase_Json::get($result));
	}

	public function getAttributeValuesAtom($request) 
	{
		$attr = Dase_DBO_Attribute::get($request->get('collection_ascii_id'),$request->get('att_ascii_id'));
		$request->renderResponse($attr->valuesAsAtom($request->get('collection_ascii_id')));
	}

}

