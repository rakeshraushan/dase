<?php
/*
 * Copyright 2008 The University of Texas at Austin
 *
 * This file is part of DASe.
 * 
 * DASe is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * DASe is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with DASe.  If not, see <http://www.gnu.org/licenses/>.
 */ 


class Dase_Acl
{
	public $auth_map = array(
		'superuser' => 3,
		'admin' => 3,
		'manager' => 3,
		'metadata' => 3,
		'write' => 3,
		'none' => 2,
		'read' => 2,
	);

	public static function generate()
	{
		$acl = array();
		$cache = Dase_Cache::get('acl');
		$data = $cache->getData(1500);
		if ($data) {
			return unserialize($data);
		} else {
			$colls = new Dase_DBO_Collection();
			foreach ($colls->find() as $c) {
				foreach ($c->getManagers() as $m) {
					$acl[$c->ascii_id]['user'][$m->dase_user_eid] = $m->auth_level;
				}
			}
			$cache->setData(serialize($acl));
			return $acl;
		}
	}

	public static function generateCollectionData()
	{
		$cdata = array();
		$cache = Dase_Cache::get('collection_data');
		$data = $cache->getData(1500);
		if ($data) {
			return $data;
		} else {
			$colls = new Dase_DBO_Collection();
			foreach ($colls->find() as $c) {
				$cdata[$c->ascii_id]['visibility'] = $c->visibility;
				$cdata[$c->ascii_id]['path_to_media_files'] = Dase_Config::get('path_to_media').'/'.$c->ascii_id;
			}
			$cache->setData($cdata);
			return $cdata;
		}
	}

	public static function retrieve($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}

	public static function check($coll,$size,$eid=null)
	{
		$cdata = Dase_Acl::getCollectionData();
		$sizes = Dase_Config::get('sizes');
		$gate = $sizes[$size];
		if (!$gate) {
			return $cdata[$coll]['path_to_media_files'];
		}
		if ($eid && 1 == $gate) { //existence of $eid indicates valid user
			return $cdata[$coll]['path_to_media_files'];
		}
		$acl = Dase_Acl::getAcl();
		if ('public' == $cdata[$coll]['visibility']) {
			return $cdata[$coll]['path_to_media_files'];
		}
		if ('user' == $cdata[$coll]['visibility'] && $eid) {
			return $cdata[$coll]['path_to_media_files'];
		}
		if (isset($acl[$coll]['user'][$eid])) {
			return $cdata[$coll]['path_to_media_files'];
		}
		return false;
	}
}

