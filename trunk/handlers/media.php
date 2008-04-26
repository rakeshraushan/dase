<?php

class MediaHandler
{
	public static function getMediaAttributes($params)
	{
		$media_atts = new Dase_DBO_MediaAttribute;
		$media_atts->orderBy('label');

		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'media/attributes.xsl';
		foreach ($media_atts->find() as $ma) { 
			$t->addSourceNode($ma->asSimpleXml());
		}
		Dase::display($t->transform());
	}

	public static function updateMediaAttribute($params)
	{
		$media_att = new Dase_DBO_MediaAttribute;
		$media_att->load($params['id']);
		$media_att->term = Dase_Filter::filterPost('term');
		$media_att->label = Dase_Filter::filterPost('label');
		$media_att->update();
		$msg = "updated media attribute";
		Dase::redirect('media/attributes',$msg);
	}
}

