<?php

class Dase_Handler_Scheme extends Dase_Handler
{
	public $resource_map = array(
		'{uri}' => 'scheme',
		'rel/{collection_ascii_id}/{parent_type_ascii_id}/to/{child_type_ascii_id}' => 'relation',
		'rel/{collection_ascii_id}/{parent_type_ascii_id}/to/{child_type_ascii_id}/form' => 'relation_form',
		'{uri1}/{uri2}' => 'scheme',
		'{uri1}/{uri2}/{uri3}' => 'scheme',
		'{uri1}/{uri2}/{uri3}/{uri4}' => 'scheme',
		'{uri1}/{uri2}/{uri3}/{uri4}/{uri5}' => 'scheme',
		'{uri1}/{uri2}/{uri3}/{uri4}/{uri5}/{uri6}' => 'scheme',
	);

	protected function setup($r)
	{
	}

	private function _getUri($r)
	{
		if ($r->has('uri')) {
			return $r->get('uri');
		}
		$uri_parts = array();
		foreach (array(1,2,3,4,5,6) as $i)  {
			if ($r->has('uri'.(string) $i)) {
				$uri_parts[] = $r->get('uri'.(string) $i);
			}	
		}
		return join('/',$uri_parts);
	}

	public function getRelationForm($r)
	{
		$r->renderResponse('working on it!');
	}

	public function getRelation($r)
	{
		$c = Dase_DBO_Collection::get($r->get('collection_ascii_id'));
		if (!$c) {
			$r->renderError(401);
		}
		$parent = Dase_DBO_ItemType::get($c->ascii_id,$r->get('parent_type_ascii_id'));
		$child = Dase_DBO_ItemType::get($c->ascii_id,$r->get('child_type_ascii_id'));
		if (!$parent || !$child) {
			$r->renderError(401);
		}
		//todo: look up in item_type_relation table!!
		$text = "This URI describes a 1:m relationship between the $parent->name type and the $child->name type";
		$r->renderResponse($text);
	}

	public function postToRelations($r)
	{
		//todo: implement this
	}

	public function getScheme($r) 
	{
		$r->response_mime_type = 'application/atom+xml';
		$scheme = new Dase_DBO_CategoryScheme;
		$scheme->uri = $this->_getUri($r);
		if (!$scheme->uri || !$scheme->findOne()) {
			$r->renderError(401);
		}
		$r->renderResponse($scheme->asAtomEntry());
	}

	public function deleteScheme($r)
	{
		//todo: http auth here for atompub
		$this->user = $r->getUser('http');
		if (!$this->user->isSuperuser()) {
			$r->renderError(401);
		}
		$scheme = new Dase_DBO_CategoryScheme;
		$scheme->uri = $this->_getUri($r);
		if (!$scheme->uri || !$scheme->findOne()) {
			$r->renderError(404);
		}
		if (count($scheme->getCategories())) {
			$r->renderError('403','scheme has associated categories');
		}
		$name = APP_ROOT.'/scheme/'.$scheme->uri;
		$scheme->delete();
		$r->renderOk("scheme \"$name\" successfully deleted");
	}

}

