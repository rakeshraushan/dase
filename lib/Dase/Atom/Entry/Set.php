<?php
class Dase_Atom_Entry_Set extends Dase_Atom_Entry
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

	function update($r) 
	{
		$user = $r->getUser();
		$atom_author = $this->getAuthorName();
		//should be exception??
		if (!$atom_author || $atom_author != $user->eid) {
			$request->renderError(401,'users do not match');
		}
		$ascii_id = $this->getAsciiId();
		$set = Dase_DBO_Tag::get($ascii_id,$user->eid);
		if (!$set) { return; }
		$set->updated = date(DATE_ATOM);
		$set->update();
		//note that ONLY mutable categories will be affected
		$set->deleteCategories();
		foreach ($this->getCategories() as $category) {
			Dase_DBO_Category::add($set,$category['scheme'],$category['term'],$category['label']);
		}
		return $set;
	}
}
