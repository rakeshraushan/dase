<?php

require_once 'Dase/DBO/Autogen/ApplicationMonitor.php';

class Dase_DBO_ApplicationMonitor extends Dase_DBO_Autogen_ApplicationMonitor 
{

	public static function listAsAtom() {
		$am = new Dase_DBO_ApplicationMonitor;
		$am->orderBy('timestamp DESC');
		$am->setLimit(100);
		$ams = $am->find();
		$feed = new Dase_Atom_Feed;
		$feed->setTitle('DASe Monitor');
		$feed->setId(APP_ROOT.'/modules/monitor');
		$feed->setUpdated(date(DATE_ATOM));
		$feed->addAuthor('DASe (Digital Archive Services)','http://daseproject.org');
		$feed->addLink(APP_ROOT.'/modules/monitor/atom','self');
		foreach ($ams as $appmon) {
			$entry = $feed->addEntry();
			$entry->setTitle($appmon->response_time);
			$entry->setContent($appmon->response_time);
			$entry->setId(APP_ROOT . '/modules/monitor/' . $appmon->id);
			$entry->setUpdated(date(DATE_ATOM,$appmon->timestamp));
			$entry->addLink(APP_ROOT.'/modules/monitor/atom/'.$appmon->id,'self');
		}
		return $feed->asXML();

	}

}
