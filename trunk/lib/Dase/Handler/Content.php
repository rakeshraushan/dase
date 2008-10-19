<?php

class Dase_Handler_Content extends Dase_Handler
{
	public $collection;
	public $resource_map = array(
		'{collection_ascii_id}' => 'collection',
		'{collection_ascii_id}/attributes' => 'attributes',
		'{collection_ascii_id}/attributes/tallies' => 'attribute_tallies',
		'{collection_ascii_id}/attributes/{filter}' => 'attributes',
		'{collection_ascii_id}/attributes/{filter}/tallies' => 'attribute_tallies',
		'{collection_ascii_id}/attribute/{att_ascii_id}/values' => 'attribute_values',
	);

	protected function setup($r)
	{
		if ($r->has('collection_ascii_id')) {
			$this->collection = Dase_DBO_Collection::get($r->get('collection_ascii_id'));
		}
	}

	public function index() {
	}

	public function edit() {
	}

	public function update() {
	}
}
