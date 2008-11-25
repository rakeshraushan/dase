<?php

require_once 'Dase/DBO/Autogen/Category.php';

class Dase_DBO_Category extends Dase_DBO_Autogen_Category 
{
	public static $excluded_schemes = array(
		'attribute/html_input_type',
		'collection',
		'collection/item_count',
		'collection/visibility',
		'entrytype',
		'error',
		'feedtype',
		'item/status', 
		'item/type',
		'position', 
		'tag/background',
		'tag/count',
		'tag/count',
		'tag/type',
		'tag/visibility',
	);

	public static function remove($entity_obj,$scheme,$term='')
	{
		//note: lots of "convention" assumed here
		//concerning table naming
		//pass in an object, scheme and (optionally) term
		$etable = $entity_obj->getTable(false);
		if (!$etable) { return; }
		$eclass = ucfirst($etable);
		$params[] = $entity_obj->id;
		$params[] = $scheme;
		$prefix = Dase_Config::get('table_prefix');
		if ($term) {
			$term_filter = "AND cat.term = ?";
			$params[] = $term;
		} else {
			$term_filter = '';
		}
		$sql = "
			SELECT e2cat.id 
			FROM {$prefix}category cat, {$prefix}{$etable}_category e2cat
			WHERE e2cat.{$etable}_id = ?
			AND cat.id = e2cat.category_id
			AND cat.scheme = ?
			$term_filter
			";
		foreach (Dase_DBO::query($sql,$params) as $row) {
			$e2cat_class = "Dase_DBO_".$eclass."Category";
			$e2cat = new $e2cat_class;
			$e2cat->load($row['id']);
			$e2cat->delete();
		}
	}

	public static function getLabel($scheme,$term)
	{
		$cat = new Dase_DBO_Category;
		$cat->scheme = $scheme;
		$cat->term = $term;
		$cat->findOne();
		return $cat->label;
	}

	public static function add($entity_obj,$scheme,$term,$label='')
	{
		//Dase_Log::debug('+++++++++++++++++++++++++'.$scheme.$term.$label);
		$etable = $entity_obj->getTable(false);
		if (!$etable) { return; }
		$eclass = ucfirst($etable);

		$cat = new Dase_DBO_Category;
		$scheme = str_replace('http://daseproject.org/category/','',$scheme);
		$cat->scheme = $scheme ? $scheme : 'none';
		$cat->term = $term;
		if (!$cat->findOne() && 
			$cat->term && 
			!in_array($scheme,Dase_DBO_Category::$excluded_schemes)) {
				if ($label) {
					$cat->label = $label;
				} else {
					$cat->label = Dase_DBO_Category::getLabel($scheme,$term);
				}
				$cat->insert();
		}
		if ($cat->id) {
			$e2cat_class = "Dase_DBO_".$eclass."Category";
			$e2cat = new $e2cat_class;
			$e2cat->category_id = $cat->id;
			$id_column = $etable."_id";
			$e2cat->$id_column = $entity_obj->id;
			$e2cat->insert();
		}
	}


	public static function set($entity_obj,$scheme,$term,$label='')
	{
		Dase_DBO_Category::remove($entity_obj,$scheme,$term);
		Dase_DBO_Category::add($entity_obj,$scheme,$term,$label);
	}

	public static function get($entity_obj,$scheme)
	{
		$etable = $entity_obj->getTable(false);
		if (!$etable) { return; }
		$eclass = ucfirst($etable);
		$params[] = $entity_obj->id;
		$params[] = $scheme;
		$prefix = Dase_Config::get('table_prefix');
		$sql = "
			SELECT cat.id, cat.scheme, cat.term, cat.label 
			FROM {$prefix}category cat, {$prefix}{$etable}_category e2cat
			WHERE e2cat.{$etable}_id = ?
			AND cat.id = e2cat.category_id
			AND cat.scheme = ?
			";
		foreach (Dase_DBO::query($sql,$params) as $row) {
			//todo: simply returns first one!!!!
			$category = new Dase_DBO_Category($row);
			return $category;
		}
	}

	public static function getAll($entity_obj)
	{
		$categories = array();
		$etable = $entity_obj->getTable(false);
		if (!$etable) { return; }
		$eclass = ucfirst($etable);
		$params[] = $entity_obj->id;
		$prefix = Dase_Config::get('table_prefix');
		$sql = "
			SELECT cat.id, cat.scheme, cat.term, cat.label 
			FROM {$prefix}category cat, {$prefix}{$etable}_category e2cat
			WHERE e2cat.{$etable}_id = ?
			AND cat.id = e2cat.category_id
			";
		foreach (Dase_DBO::query($sql,$params) as $row) {
			$category = new Dase_DBO_Category($row);
			$categories[] = $category;
		}
		return $categories;
	}

	public function getScheme()
	{
		if (!$this->scheme || 'none' == $this->scheme) {
			return '';
		}
		if ('http' == substr($this->scheme,0,4)) {
			return $this->scheme;
		} else {
			return 'http://daseproject.org/category/'.$this->scheme;
		}
	}

	public static function listAsXml()
	{
		$atom_cats = new Dase_Atom_Categories;
		$atom_cats->setFixed('no');
		$cats = new Dase_DBO_Category;
		foreach ($cats->find() as $cat) {
			$atom_cats->addCategory($cat->term,$cat->getScheme(),$cat->label);
		}
		return $atom_cats->asXml();

	}
}