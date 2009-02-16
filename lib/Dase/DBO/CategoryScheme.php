<?php

require_once 'Dase/DBO/Autogen/CategoryScheme.php';

class Dase_DBO_CategoryScheme extends Dase_DBO_Autogen_CategoryScheme 
{
	public static function listAsFeed($order_by='created DESC')
	{
		$schemes = new Dase_DBO_CategoryScheme;
		$schemes->orderBy($order_by);
		$feed = new Dase_Atom_Feed;
		$feed->setId();
		$feed->setTitle('List of Available Category Schemes');
		$feed->setUpdated(Dase_DBO_CategoryScheme::getLastCreated());
		$feed->addAuthor();
		$feed->setFeedType('category_scheme_list');
		foreach ($schemes->find() as $sch) {
			$e = $feed->addEntry('category_scheme');
			$e->setId('{APP_ROOT}/category/'.$sch->uri);
			$e->addLink('{APP_ROOT}/category/'.$sch->uri,'edit' );
			$e->setUpdated($sch->created);
			$e->addAuthor($sch->created_by_eid);
			$e->setTitle($sch->name);
			$e->addCategory($sch->applies_to,'http://daseproject.org/category/applies_to');
			$e->setSummary($sch->description);
			$e->setContentXml($sch->asXml(),'application/atomcat+xml');
		}
		return $feed->asXml();
	}

	public function getCategories()
	{
		$res = array();
		$cats = new Dase_DBO_Category;
		$cats->scheme_id = $this->id;
		foreach ($cats->find() as $cat) {
			$res[] = clone $cat;
		}
		return $res;
	}

	public function asXml()
	{
		$scheme = new Dase_Atom_Categories();
		if ($this->fixed) {
			$scheme->setFixed('yes');
		} else {
			$scheme->setFixed('no');
		}
		$scheme->setScheme('http://daseproject.org/category/'.$this->uri);
		//$scheme->dom->formatOutput = true;
		return $scheme->asXml();
	}


	static function getLastCreated()
	{
		$prefix = $this->db->table_prefix;
		$sql = "
			SELECT created
			FROM {$prefix}category_scheme
			ORDER BY created DESC
			";
		//returns first non-null created
		foreach (Dase_DBO::query($sql) as $row) {
			if ($row['created']) {
				return $row['created'];
			}
		}
	}
}
