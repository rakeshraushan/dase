<?php

class MediaHandler
{
	public static function getMediaAttributes($request)
	{
		$media_atts = new Dase_DBO_MediaAttribute;
		$media_atts->orderBy('label');
		$t = new Dase_Template($request);
		$t->assign('attributes',$media_atts->find());  
		$request->renderResponse($t->fetch('media/attributes.tpl'));
	}

	public static function updateMediaAttribute($request)
	{
		$media_att = new Dase_DBO_MediaAttribute;
		$media_att->load($params['id']);
		$media_att->term = Dase_Filter::filterPost('term');
		$media_att->label = Dase_Filter::filterPost('label');
		$media_att->update();
		$msg = "updated media attribute";
		$request->renderRedirect('media/attributes',$msg);
	}

	public static function get($request) {
		Dase_Auth::authorize('read',$params);


	}
}

