<?php

require_once 'Dase/DBO.php';

/*
 * DO NOT EDIT THIS FILE
 * it is auto-generated by the
 * script 'bin/class_gen.php
 * 
 */

class Dase_DBO_Autogen_DaseUser extends Dase_DBO 
{
	public function __construct($db,$assoc = false) 
	{
		parent::__construct($db,'dase_user', array('backtrack','cb','created','current_collections','current_search_cache_id','display','eid','has_access_exception','last_action','last_cb_access','last_item','max_items','name','service_key_md5','template_composite','updated'));
		if ($assoc) {
			foreach ( $assoc as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}