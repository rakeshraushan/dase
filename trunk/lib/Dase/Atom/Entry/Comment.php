<?php
class Dase_Atom_Entry_Comment extends Dase_Atom_Entry
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

	function addInReplyTo($ref,$type,$href)
	{
		$irt = $this->addElement('thr:in-reply-to',null,Dase_Atom::$ns['thr']);
		$irt->setAttribute('ref',$ref);
		$irt->setAttribute('type',$type);
		$irt->setAttribute('href',$href);
		return $irt;	
	}
}
