<?php
class Dase_Atom_Entry_Attribute extends Dase_Atom_Entry
{
	function __construct($dom=null,$root=null)
	{
		parent::__construct($dom,$root);
	}

	function insert($request,$collection) 
	{
		$att = new Dase_DBO_Attribute;
		$att->attribute_name = $this->getTitle();
		$att->ascii_id = $this->getAsciiId();
		if (!Dase_DBO_Attribute::get($collection->ascii_id,$att->ascii_id)) {
			$att->collection_id = $collection->id;
			$att->updated = date(DATE_ATOM);
			$att->sort_order = 9999;
			$att->is_on_list_display = 1;
			$att->is_public = 1;
			$att->in_basic_search = 1;
			$att->html_input_type = 'text';
			$att->insert();
			$att->resort();
		} else {
			throw new Dase_Exception('attribute exists');
		}
		return $att;
	}

	/** used w/ PUT request 
	 */
	function update($request,$collection) 
	{
		throw new Exception('not yet implemented');
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
