<?php
class Dase_Atom_Feed_CollectionList extends Dase_Atom_Feed 
{
	function __construct($xml = null)
	{
		parent::__construct($xml);
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
