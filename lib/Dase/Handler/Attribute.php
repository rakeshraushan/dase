<?php

class Dase_Handler_Attribute extends Dase_Handler
{
	public $attribute;
	public $collection;
	public $resource_map = array(
		'admin_{att_ascii_id}' => 'admin_attribute',
		'{collection_ascii_id}/{att_ascii_id}' => 'attribute',
		'{collection_ascii_id}/{att_ascii_id}/values' => 'attribute_values',
		'{collection_ascii_id}/{att_ascii_id}/defined' => 'defined_values',
	);

	public function setup($r)
	{
		$db = $r->retrieve('db');
		$this->db = $db;
		if ($r->has('collection_ascii_id')) {
			$this->collection = Dase_DBO_Collection::get($db,$r->get('collection_ascii_id'));
		}
		if ($r->has('att_ascii_id') && $r->has('collection_ascii_id')) {
			$this->attribute = Dase_DBO_Attribute::get($db,$r->get('collection_ascii_id'),$r->get('att_ascii_id'));
		} 
		if ($r->has('att_ascii_id') && !$r->has('collection_ascii_id')) {
			$this->attribute = Dase_DBO_Attribute::getAdmin($db,'admin_'.$r->get('att_ascii_id'));
		} 
		if (!$this->attribute) {
			$r->renderError('404');
		}
	}

	public function getAdminAttribute($r)
	{
		$r->renderResponse($r->get('att_ascii_id'));
	}

	public function getAttributeJson($r) 
	{
		$r->renderResponse($this->attribute->asJson());
	}

	public function getAttributeAtom($r) 
	{
		$entry = new Dase_Atom_Entry;
		$r->renderResponse($this->attribute->injectAtomEntryData($entry)->asXml());
	}

	/** implicit 1000 limit */
	public function getAttributeValuesJson($r) 
	{
		$attr = Dase_DBO_Attribute::get($this->db,$r->get('collection_ascii_id'),$r->get('att_ascii_id'));
		if (0 == $attr->collection_id) {
			//since it is admin att we need to be able to limit to items in this coll
			$values_array = $attr->getDisplayValues($this->collection->ascii_id);
		} else {
			$values_array = $attr->getDisplayValues();
		}
		$result['att_name'] = $attr->attribute_name;
		$result['att_ascii'] = $attr->ascii_id;
		$result['coll'] = $r->get('collection_ascii_id');
		$result['values'] = $values_array;
		$r->renderResponse(Dase_Json::get($result));
	}

	public function getAttributeValuesAtom($r) 
	{
		$attr = Dase_DBO_Attribute::get($this->db,$r->get('collection_ascii_id'),$r->get('att_ascii_id'));
		$key = '';
		$val = '';
		if ($r->has('filter_key') && $r->has('filter_value')) {
			$key = $r->get('filter_key');
			$val = $r->get('filter_value');
		}
		$r->renderResponse($attr->valuesAsAtom($r->get('collection_ascii_id'),$key,$val,$r->app_root));
	}
}

