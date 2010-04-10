<?php

class Dase_ModuleHandler_Jsoneditor extends Dase_Handler {

	public $resource_map = array(
		'/' => 'editor',
		'index' => 'editor',
		'editor' => 'editor',
	);

	public function getEditor($r) 
	{
		$user = $r->getUser();
		$tpl = new Dase_Template($r,true);
		$tpl->assign('collection',Dase_Atom_Feed::retrieve($r->app_root.'/user/'.$user->eid.'/json_lists/recent.atom',$user->eid,$user->getHttpPassword($r->getAuthToken())));
		$r->renderResponse($tpl->fetch('editor.tpl'));
	}
}
