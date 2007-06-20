<?php

require_once 'Dase/Remote/Client.php';

class Dase_Remote_Item 
{
	public static function get($ser_num,$ascii_id,$site) {
		$remote = new Dase_Remote_Client($site);
		$remote->setPath("collection/$ascii_id/item/$ser_num");
		return $remote->getXml();
	}
}
