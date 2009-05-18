<?php

require_once 'Dase/DBO/Autogen/JobQueue.php';

class Dase_DBO_JobQueue extends Dase_DBO_Autogen_JobQueue 
{
	public function queueTask($eid,$method,$args=array()) 
	{
		if (method_exists($this,$method)) {
			$this->dase_user_eid = $eid;
			$this->method = $method;
			$this->created = date(DATE_ATOM);
			$this->run = 'pending';
			$this->args = serialize($args);
			$this->insert();
		} else {
			throw new Exception("no such task");
		}
	}

	public static function runTasks($db)
	{

		$jobs = new Dase_DBO_JobQueue($db);
		$jobs->run = 'pending';
		$jobs->orderBy('created');
		foreach ($jobs->find() as $task) {
			$method = $task->method;
			$args = unserialize($task->args);
			$task->run = date(DATE_ATOM);
			$out = call_user_func_array(array($task,$method),$args);
			if ($out) {
				$task->completed = date(DATE_ATOM);
				$task->msg = $out;
			} else {
				$task->msg = 'did not complete';
			}
			$task->update();
		}
	}

	public static function runNextTask($db,$eid='')
	{
		$next = new Dase_DBO_JobQueue($db);
		$next->run = 'pending';
		if ($eid) {
			$next->eid = $eid;
		}
		$next->orderBy('created');
		if ($next->findOne()) {
			//checkout
			$next->run = date(DATE_ATOM);
			$next->update();
			$method = $task->method;
			$args = unserialize($task->args);
			$out = call_user_func_array(array($task,$method),$args);
			if ($out) {
				$next->completed = date(DATE_ATOM);
				$next->msg = $out;
			} else {
				$next->msg = 'did not complete';
			}
			$next->update();
		}
	}

	public function fixItemsMissingColl()
	{
		//this should only be necessary while old DASe still operating
		$items = new Dase_DBO_Item($this->db);
		$items->addWhere('p_collection_ascii_id','null','is');
		$i=0;
		foreach ($items->find() as $item) {
			$c = $item->getCollection();
			$item->p_collection_ascii_id = $c->ascii_id;
			if ($item->update()) {
				$i++;
			}
		}
		return $i;
	}

	public function testEmail($to,$subject,$msg)
	{
		if (mail($to,$subject,$msg)) {
			return 'sent msg to '.$to;
		} else {
			return false;
		}
	}
}
