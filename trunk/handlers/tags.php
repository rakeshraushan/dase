<?php

class TagsHandler extends Dase_Handler
{

	public $resource_map = array( 
		'/' => 'tags',
	);

	protected function setup($request)
	{
	}	

	public function postToTags($request)
	{
		$tag_name = $request->get('tag_name');
		//todo: make this work w/ cookie OR http auth??
		$user = $request->getUser();
		$tag = Dase_DBO_Tag::create($tag_name,$user);
		if ($tag) {
			//todo: should send a 201 w/ location header
			$request->renderResponse('Created "'.$tag_name.'"');
		} else {
			$request->renderError(409,'Please choose another name.');
		}
	}

}

