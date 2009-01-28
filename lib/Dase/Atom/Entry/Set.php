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

	/** used by Dase_Handler_Tag::putEdit() */
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

		$cats = $this->getCategoriesByScheme('http://daseproject.org/category/visibility');
		if (count($cats)) {
			$vis = $cats[0]['term'];
		}
		$set->visibility = $vis;
		if ('public' == $vis) {
			$set->is_public = 1;
		}
		if ('private' == $vis) {
			$set->is_public = 0;
		}

		$set->updated = date(DATE_ATOM);
		$set->update();
		//note that ONLY mutable categories will be affected
		$set->deleteCategories();
		foreach ($this->getCategories() as $category) {
			Dase_DBO_Category::add($set,$category['scheme'],$category['term']);
		}
		return $set;
	}

	public function insert($r)
	{
		$user = $r->getUser();
		$atom_author = $this->getAuthorName();
		//should be exception??
		if (!$atom_author || $atom_author != $user->eid) {
			$request->renderError(401,'users do not match');
		}
		$set = new Dase_DBO_Tag;
		$set->ascii_id = $this->getAsciiId();
		$set->eid = $user->eid;
		if ($set->findOne()) { 
			$r->renderError(409,'set with that name exists');
		}
		$set->dase_user_id = $user->id;
		$set->name = $this->getTitle();
		$set->is_public = 0;
		$set->type= 'set';
		$set->created = date(DATE_ATOM);
		$set->updated = date(DATE_ATOM);
		$set->insert();
		/*
		foreach ($this->getCategories() as $category) {
			Dase_DBO_Category::add($set,$category['scheme'],$category['term'],$category['label']);
		}
		 */
		return $set;
	}
}
