<?php

require_once 'Dase/DBO/Autogen/Category.php';

class Dase_DBO_Category extends Dase_DBO_Autogen_Category 
{

	public static function remove($entity_obj,$scheme,$term='')
	{
		//note: lots of "convention" assumed here
		//concerning table naming
		//pass in an object, scheme and (optionally) term
		$etable = $entity_obj->getTable();
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
		$etable = $entity_obj->getTable();
		if (!$etable) { return; }
		$eclass = ucfirst($etable);

		$cat = new Dase_DBO_Category;
		$cat->scheme = $scheme ? $scheme : 'none';
		$cat->term = $term;
		if (!$cat->findOne() && $cat->term) {
			if ($label) {
				$cat->label = $label;
			} else {
				$cat->label = Dase_DBO_Category::getLabel($scheme,$term);
			}
			$cat->insert();
		}
		$e2cat_class = "Dase_DBO_".$eclass."Category";
		$e2cat = new $e2cat_class;
		$e2cat->category_id = $cat->id;
		$id_column = $etable."_id";
		$e2cat->$id_column = $entity_obj->id;
		$e2cat->insert();
	}


	public static function set($entity_obj,$scheme,$term,$label='')
	{
		Dase_DBO_Category::remove($entity_obj,$scheme,$term);
		Dase_DBO_Category::add($entity_obj,$scheme,$term,$label);
	}

	public static function get($entity_obj,$scheme)
	{
		$etable = $entity_obj->getTable();
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
}
