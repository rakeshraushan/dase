<?php

class Dase_UserCookie {

/* following REST principles, the client holds the 
 * application state and cookie are OK when the 
 * CLIENT has control over them, NOT the server.
 * This cookie is ONLY set when an AuthCookie is
 * set and simply provides a convienient bit of data
 * (the user's eid) that can be used by the client for 
 * personalization, either by echo-ing the eid on 
 * page OR ajaxily (and w/ a URI that includes the eid)
 * grabbing more data to add to the page.
 *
 * Note that this cookie is write-only! Data from client
 * MUST come from uri string or request body 
 *
 */

	static $cookiename = 'DASE_USER';

	public function __construct($userid) {
		setcookie(self::$cookiename,$userid,0,'/');
	}

	public function delete() {
		//this is cheating a bit, since the client should 
		//delete its own cookie (??)
		setcookie(self::$cookiename,"",-86400,'/');
	}
}


