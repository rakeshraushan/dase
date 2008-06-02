<?php

class MediaHandler extends Dase_Handler
{
	public $resource_map = array(
		'attributes' => 'attributes',
		'{collection_ascii_id}/{size}/{serial_number}' => 'file',
	);

	protected function setup($request)
	{
	}

	public function getFileJpg($request)
	{
		$collection_ascii_id = $request->get('collection_ascii_id');
		$serial_number = $request->get('serial_number');
		$size = $request->get('size');
		$request->serveFile($this->getFilePath($collection_ascii_id,$serial_number,$size,$request->format),$request->response_mime_type);
	}

	private function getFilePath($collection_ascii_id,$serial_number,$size,$format)
	{
		$sizes = array(
			'thumbnail' => array( 'dir' => 'thumbnails'),
			'viewitem' => array( 'dir' => '400'),
			'small' => array( 'dir' => 'small'),
			'medium' => array( 'dir' => 'medium'),
			'large' => array( 'dir' => 'large'),
			'full' => array( 'dir' => 'full'),
		);
		$path = '/mnt/www-data/dase/media/'.
			$collection_ascii_id.'/'.
			$sizes[$size]['dir'].'/'.
			$serial_number.'.'.$format;
		return $path;
	}

	public function getMediaAttributes($request)
	{
		$media_atts = new Dase_DBO_MediaAttribute;
		$media_atts->orderBy('label');
		$t = new Dase_Template($request);
		$t->assign('attributes',$media_atts->find());  
		$request->renderResponse($t->fetch('media/attributes.tpl'));
	}

	public function putMediaAttribute($request)
	{
		$media_att = new Dase_DBO_MediaAttribute;
		$media_att->load($params['id']);
		$media_att->term = $request->get('term');
		$media_att->label = $request->get('label');
		$media_att->update();
		$msg = "updated media attribute";
		$request->renderRedirect('media/attributes',$msg);
	}

}

