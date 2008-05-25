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

class Dase_Admin 
{

	public static function getAcl() 
	{
		$acl = array();
		$cms = new Dase_DBO_CollectionManager;
		foreach ($cms->find() as $cm) {
			$cm->dase_user_eid = strtolower($cm->dase_user_eid);
			$acl['collections'][$cm->collection_ascii_id][$cm->dase_user_eid] = $cm->auth_level;
		}
		$users = new Dase_DBO_DaseUser;
		foreach ($users->find() as $user) {
			$user->eid = strtolower($user->eid);
			$acl['users'][$user->eid] = $user->id;
		}
		/*
		$tags = new Dase_DBO_Tag;
		foreach ($tags->find() as $tag) {
			if ($tag->getUser()) {
				$eid = strtolower($tag->user->eid);
			} else {
				$eid = 'no eid on record';
			}
			$acl['tags'][$tag->ascii_id][$eid] = $tag->id;
		}
		 */
		return $acl;
	}
}

