<?php

class Dase_Atom_Entry_User extends Dase_Atom_Entry
{
	function __construct($dom=null,$root=null)
	{
		parent::__construct($dom,$root);
	}

	function __get($var) {
		//allows smarty to invoke function as if getter
		$classname = get_class($this);
		$method = 'get'.ucfirst($var);
		if (method_exists($classname,$method)) {
			return $this->{$method}();
		} else {
			return parent::__get($var);
		}
	}

	public function getEid()
	{
		return $this->getAsciiId();
	}

	public function insert($r)
	{
		$user = new Dase_DBO_DaseUser;
		$user->eid = $this->getEid();
		if ($user->findOne()) { 
			//could prob just throw exception here
			$r->renderError(409,'user exists');
		}
		$user->name = $this->title;
		$user->created = date(DATE_ATOM);
		$user->updated = date(DATE_ATOM);
		$user->insert();
		return $user;
	}
}
