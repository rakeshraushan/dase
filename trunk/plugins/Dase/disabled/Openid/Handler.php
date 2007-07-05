<?php

Class Dase_Openid_Handler
{
	private function __construct() {}

	public static function index() {
		echo "hello from the openid plugin"; exit;
	}
	public static function login() {
		require_once "Auth/OpenID/Consumer.php";
		require_once "Auth/OpenID/FileStore.php";
		$store_path = "/usr/local/dase/tmp/_php_consumer_test";
		if (!file_exists($store_path) &&
				!mkdir($store_path)) {
			print "Could not create the FileStore directory '$store_path'. ".
				" Please check the effective permissions.";
			exit(0);
		}
		$store = new Auth_OpenID_FileStore($store_path);
		$consumer = new Auth_OpenID_Consumer($store);

		session_start();

		// Render a default page if we got a submission without an openid
		// value.
		if (empty($_GET['openid'])) {
			Dase::reload('/',"Expected an OpenID URL.");
		}

		$scheme = 'http';
		if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') {
			$scheme .= 's';
		}

		$openid = $_GET['openid'];
		$process_url =  APP_ROOT . 'openid/login_finish';
		$trust_root = APP_ROOT;

		// Begin the OpenID authentication process.
		$auth_request = $consumer->begin($openid);

		// Handle failure status return values.
		if (!$auth_request) {
			Dase::reload('/',"Authentication error.");
		}

		$auth_request->addExtensionArg('sreg', 'optional', 'email');

		// Redirect the user to the OpenID server for authentication.  Store
		// the token for this authentication so we can verify the response.

		$redirect_url = $auth_request->redirectURL($trust_root,
				$process_url);

		header("Location: ".$redirect_url);
	}

	public static function loginFinish() {
		require_once "Auth/OpenID/Consumer.php";
		require_once "Auth/OpenID/FileStore.php";
		$store_path = "/usr/local/dase/tmp/_php_consumer_test";
		if (!file_exists($store_path) &&
				!mkdir($store_path)) {
			print "Could not create the FileStore directory '$store_path'. ".
				" Please check the effective permissions.";
			exit(0);
		}
		$store = new Auth_OpenID_FileStore($store_path);
		$consumer = new Auth_OpenID_Consumer($store);

		session_start();

		// Complete the authentication process using the server's response.
		$response = $consumer->complete($_GET);

		if ($response->status == Auth_OpenID_CANCEL) {
			// This means the authentication was cancelled.
			echo 'Verification cancelled.';exit;
		} else if ($response->status == Auth_OpenID_FAILURE) {
			$msg = "OpenID authentication failed: " . $response->message;
			echo "$msg";exit;
		} else if ($response->status == Auth_OpenID_SUCCESS) {
			// This means the authentication succeeded.
			$openid = $response->identity_url;
			$esc_identity = htmlspecialchars($openid, ENT_QUOTES);
			$success = sprintf('You have successfully verified ' .
					'<a href="%s">%s</a> as your identity.',
					$esc_identity, $esc_identity);

			if ($response->endpoint->canonicalID) {
				$success .= '  (XRI CanonicalID: '.$response->endpoint->canonicalID.') ';
			}

			$sreg = $response->extensionResponse('sreg');

			if (@$sreg['email']) {
				$success .= "  You also returned '".$sreg['email']."' as your email.";
			}
			if (@$sreg['postcode']) {
				$success .= "  Your postal code is '".$sreg['postcode']."'";
			}
		}
		$username = $esc_identity;
		$new_user = new Dase_DB_DaseUser;
		$new_user->name = "$username (openid)";
		$new_user->eid = $username;
		$new_user->insert();
		$user = Dase_User::check_credentials($username,'pass');
		if ($user) {
			$cookie = new Dase_AuthCookie($user->id);
			$cookie->set();
			Dase::reload();
		} else {
			Dase::reload('error','there was an authentication problem');
		}
	}

}

