<?php

class Dase_Handler_Category extends Dase_Handler
{
	public $resource_map = array(
		'list' => 'categories',
		'{uri}' => 'scheme',
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

	public function getCategories($r)
	{
		$r->response_mime_type = 'application/xml';
		$r->renderResponse(Dase_DBO_Category::asList($this->db));

	}

	public function getScheme($r) 
	{
		$r->response_mime_type = 'application/atom+xml';
		$scheme = new Dase_DBO_CategoryScheme($this->db);
		$scheme->uri = $this->_getUri($r);
		if (!$scheme->uri || !$scheme->findOne()) {
			$r->renderError(401);
		}
		//todo:  asAtomEntry does not exist....
		$r->renderResponse($scheme->asAtomEntry($r->app_root));
	}

	public function deleteScheme($r)
	{
		//todo: http auth here for atompub
		$this->user = $r->getUser('http');
		if (!$this->user->isSuperuser($r->retrieve('config')->getSuperusers())) {
			$r->renderError(401);
		}
		$scheme = new Dase_DBO_CategoryScheme($this->db);
		$scheme->uri = $this->_getUri($r);
		if (!$scheme->uri || !$scheme->findOne()) {
			$r->renderError(404);
		}
		if (count($scheme->getCategories())) {
			$r->renderError('403','scheme has associated categories');
		}
		$name = $r->app_root.'/scheme/'.$scheme->uri;
		$scheme->delete();
		$r->renderOk("scheme \"$name\" successfully deleted");
	}

}

