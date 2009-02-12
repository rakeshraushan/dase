<?php

/** used for those cases when an app:categories doc is passed as content in an atom entry */
class Dase_Atom_Entry_CategoryScheme extends Dase_Atom_Entry
{
	function __construct($dom=null,$root=null)
	{
		parent::__construct($dom,$root);
	}

	public function getScheme()
	{
		return $this->getContentXmlNode()->getAttribute('scheme');
	}

	public function getFixed()
	{
		return $this->getContentXmlNode()->getAttribute('fixed');
	}

	public function getAppliesTo()
	{
		foreach ($this->getCategoriesByScheme('http://daseproject.org/category/applies_to') as $cat) {
			return $cat['term'];
		}
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
