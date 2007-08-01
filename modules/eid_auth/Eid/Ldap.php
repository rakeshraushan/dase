<?php

Class Dase_Eid_Ldap
{
	private function __construct() {}

	public static function lookup($query,$type) {
		$person_array = array();
		$x500 = ldap_connect('ldap.utexas.edu');
		$bind = ldap_bind($x500);
		$dn = "ou=people,dc=directory,dc=utexas,dc=edu";
		$filter = "$type=$query";
		$ldap_result = @ldap_search($x500,$dn,$filter);
		if ($ldap_result) {
			$entry_array = ldap_get_entries($x500, $ldap_result);
			for ($i=0; $i < count($entry_array) - 1;$i++) {
				if ($entry_array[$i]) {
					$eid = $entry_array[$i]['uid'][0];
					$person_array[$eid]['eid'] = $eid;
					if (isset($entry_array[$i]['mail'])) {
						$person_array[$eid]['email'] = $entry_array[$i]['mail'][0];
					}

					if (isset($entry_array[$i]['cn'])) {
						$person_array[$eid]['name'] = $entry_array[$i]['cn'][0];
					}
				}
			}
			ldap_close($x500);
			return $person_array;
		}
	}
}

