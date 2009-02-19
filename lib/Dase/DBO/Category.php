<?php

require_once 'Dase/DBO/Autogen/Category.php';

class Dase_DBO_Category extends Dase_DBO_Autogen_Category 
{
	public static $excluded_schemes = array(
		'attribute/html_input_type',
		'collection',
		'entrytype',
		'error',
		'feedtype',
		'status', 
		'item_type',
		'position', 
		'background',
		'item_count',
		'tag_type',
		'visibility',
	);

	public static function asList($db)
	{
		$atomcats = new Dase_Atom_Categories;
		$cats = new Dase_DBO_Category($db);
		foreach ($cats->find() as $c) {
			$scheme = $c->getScheme();
			$atomcats->addCategory($c->term,$scheme,$c->label);
		}
		return $atomcats->asXml();
	}

	public static function remove($db,$entity_obj,$scheme_id,$term='')
	{
		//note: lots of "convention" assumed here
		//concerning table naming
		//pass in an object, scheme_id and (optionally) term
		$etable = $entity_obj->getTable(false);
		if (!$etable) { return; }
		$eclass = ucfirst($etable);
		$params[] = $entity_obj->id;
		$params[] = $scheme_id;
		$prefix = $db->table_prefix;
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
			AND cat.scheme_id = ?
			$term_filter
			";
		foreach (Dase_DBO::query($db,$sql,$params) as $row) {
			$e2cat_class = "Dase_DBO_".$eclass."Category";
			$e2cat = new $e2cat_class;
			$e2cat->load($row['id']);
			$e2cat->delete();
		}
	}

	public static function getLabel($db,$scheme,$term)
	{
		$prefix = $this->db->table_prefix;
		$sql = "
			SELECT cat.label 
			FROM {$prefix}category cat, {$prefix}category_scheme csh
			WHERE cat.term = ?
			AND csh.id = cat.scheme_id
			AND csh.uri = ?
			";
		return Dase_DBO::query($db,$sql,array($term,$scheme))->fetchColumn();
	}

	public static function add($db,$entity_obj,$scheme,$term,$label='')
	{
		//Dase_Log::get()->debug('+++++++++++++++++++++++++'.$scheme.$term.$label);
		$etable = $entity_obj->getTable(false);
		if (!$etable) { return; }
		$eclass = ucfirst($etable);

		$cat = new Dase_DBO_Category($db);
		$scheme = str_replace('http://daseproject.org/category/','',$scheme);
		$cs = new Dase_DBO_CategoryScheme($db);
		$cs->uri = trim($scheme,'/');
		if ($cs->findOne()) {
			$cat->scheme_id = $cs->id;
		} else {
			$cat->scheme_id = 0;
		}
		$cat->term = $term;
		if (!$cat->findOne() && $cat->term && !in_array($scheme,Dase_DBO_Category::$excluded_schemes)) {
			if ($label) {
				$cat->label = $label;
			} else {
				$cat->label = Dase_DBO_Category::getLabel($scheme,$term);
			}
			$cat->insert();
		}
		if ($cat->id) {
			$e2cat_class = "Dase_DBO_".$eclass."Category";
			$e2cat = new $e2cat_class($db);
			$e2cat->category_id = $cat->id;
			$id_column = $etable."_id";
			$e2cat->$id_column = $entity_obj->id;
			$e2cat->insert();
		}
	}


	public static function set($db,$entity_obj,$scheme,$term,$label='')
	{
		Dase_DBO_Category::remove($db,$entity_obj,$scheme,$term);
		Dase_DBO_Category::add($db,$entity_obj,$scheme,$term,$label);
	}

	public static function get($db,$entity_obj,$scheme)
	{
		$etable = $entity_obj->getTable(false);
		if (!$etable) { return; }
		$eclass = ucfirst($etable);
		$params[] = $entity_obj->id;
		$params[] = $scheme;
		$prefix = $db->table_prefix;
		$sql = "
			SELECT cat.id, csh.uri as scheme, cat.term, cat.label 
			FROM {$prefix}category cat, {$prefix}{$etable}_category e2cat, {$prefix}category_scheme csh
			WHERE e2cat.{$etable}_id = ?
			AND cat.id = e2cat.category_id
			AND csh.uri = ?
			AND csh.id = cat.scheme_id
			";
		foreach (Dase_DBO::query($db,$sql,$params) as $row) {
			//todo: simply returns first one!!!!
			$category = new Dase_DBO_Category($db,$row);
			return $category;
		}
	}

	public static function getAll($db,$entity_obj)
	{
		$categories = array();
		$etable = $entity_obj->getTable(false);
		if (!$etable) { return; }
		$eclass = ucfirst($etable);
		$params[] = $entity_obj->id;
		$prefix = $this->db->table_prefix;
		$sql = "
			SELECT cat.id, csh.id as scheme_id, cat.term, cat.label 
			FROM {$prefix}category cat, {$prefix}{$etable}_category e2cat, {$prefix}category_scheme csh
			WHERE e2cat.{$etable}_id = ?
			AND cat.id = e2cat.category_id
			AND csh.id = cat.scheme_id
			GROUP BY cat.id, csh.id, term, label
			";
		foreach (Dase_DBO::query($db,$sql,$params) as $row) {
			$category = new Dase_DBO_Category($db,$row);
			$categories[] = $category;
		}
		return $categories;
	}

	public function getScheme()
	{
		if (!$this->scheme_id) {
			return '';
		}
		$scheme = new Dase_DBO_CategoryScheme($this->db);
		$scheme->load($this->scheme_id);
		return 'http://daseproject.org/category/'.$scheme->uri;
	}

}
