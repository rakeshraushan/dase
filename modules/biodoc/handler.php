<?php

class Dase_ModuleHandler_Biodoc extends Dase_Handler 
{
	public $resource_map = array(
		'/' => 'index',
		'index' => 'index',
		'orig' => 'orig',
		'topics' => 'topics',
		'about' => 'about',
		'contribute' => 'contribute',
		'emailer' => 'emailer',
		'plugin' => 'plugin',
		'contact' => 'contact',
		'search' => 'search',
	);

	public function setup($r)
	{
	}

	public function getOrig($r) 
	{
		$t = new Dase_Template($r,true);
		$r->renderResponse($t->fetch('orig.tpl'));
	}

	public function getIndex($r) 
	{
		$t = new Dase_Template($r,true);
		//$t->assign('feed',Dase_Atom_Feed::retrieve($r->app_root."/search.atom?q=host:wheat"));
		$r->renderResponse($t->fetch('index.tpl'));
	}

	public function getSearch($r) 
	{
		$t = new Dase_Template($r,true);
		$unit = urlencode($r->get('unit'));
		$topic = urlencode($r->get('topic'));
		$feed = Dase_Atom_Feed::retrieve($r->app_root.'/search.atom?q=c:biodoc+unit:"'.$unit.'"+topic:"'.$topic.'"&max=9999');
		$t->assign('feed',$feed);
		$r->renderResponse($feed->asXml());
	}

	public function getTopicsJson($r) 
	{
		$unit = $r->get('unit');
		$r->renderResponse($unit);
	}

	public function getAbout($r) 
	{
		$t = new Dase_Template($r,true);
		$r->renderResponse($t->fetch('about.tpl'));
	}

	public function getContribute($r) 
	{
		$t = new Dase_Template($r,true);
		$r->renderResponse($t->fetch('contribute.tpl'));
	}

	public function postToEmailer($r) 
	{
		$mail['title'] =       $r->get('txtTitle');
		$mail['type'] =        $r->get('txtType');
		$mail['description'] = $r->get('txtDescription');
		$mail['unit'] =        $r->get('txtUnit');
		$mail['topic'] =       $r->get('txtTopic');
		$mail['keywords'] =    $r->get('txtKeywords');
		$mail['format'] =      $r->get('txtFormat');
		$mail['plugin'] =      $r->get('txtPlugin');
		$mail['size'] =        $r->get('txtSize');
		$mail['duration'] =    $r->get('txtDuration');
		$mail['url'] =         $r->get('txtURL');
		$mail['name'] =        $r->get('txtName');
		$mail['affiliation'] = $r->get('txtAffiliation');
		$mail['email'] =       $r->get('txtEmail');
		$mail['acknowledg'] =  $r->get('txtAcknowledgements');
		$body = '';
		foreach ($mail as $k => $v) {
			$body .= $k.': '.$v."\n";
		}
		$address = 'pkeane@mail.utexas.edu';
		$headers = 'From: biodoc-user-contribution@bio-doc.org' . "\r\n" .
				    'X-Mailer: PHP/' . phpversion();

		mail($address,'biodoc suggestion',$body,$headers);

		$params['msg'] = 'Thank you for your suggestion';
		$r->renderRedirect($r->module_root.'/index',$params);
	}

	public function getPlugin($r) 
	{
		$t = new Dase_Template($r,true);
		$r->renderResponse($t->fetch('plugin.tpl'));
	}

	public function getContact($r) 
	{
		$t = new Dase_Template($r,true);
		$r->renderResponse($t->fetch('contact.tpl'));
	}
}
