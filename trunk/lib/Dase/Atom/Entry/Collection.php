<?php
class Dase_Atom_Entry_Collection extends Dase_Atom_Entry
{
	function __construct($dom=null,$root=null)
	{
		parent::__construct($dom,false,$root);
	}

	function getName() 
	{
		//how name is modelled in Atom
		return $this->getTitle();
	}

	function getAscii_id() 
	{
		//how ascii_id is modelled in Atom
		return $this->getContent();
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
}
