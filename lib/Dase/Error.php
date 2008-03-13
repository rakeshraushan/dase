<?php

class Dase_Error
{

	//this class facilitates returning error messages
	//w/ proper http header
	//todo: make it work with command line (not just web)

	public static function report($code) {
		$msg = "";
		if (400 == $code) {
			header("HTTP/1.1 400 Bad Request");
			$msg = 'Bad Request';
		}
		if (404 == $code) {
			header("HTTP/1.1 404 Not Found");
			$msg = '404 not found';
		}
		if (401 == $code) {
			header('HTTP/1.1 401 Unauthorized');
			$msg = 'Unauthorized';
		}
		if (500 == $code) {
			header('HTTP/1.1 500 Internal Server Error');
		}
		$t = new Dase_Xslt;
		if (defined('DEBUG')) {
			//create an XML doc w/ DASe members
			//AND current routes
			$sx = simplexml_load_string('<errors/>');
			$d_atts = $sx->addChild('dase');
			$d_atts->addChild('http_error_code',$code);
			foreach (array('action','handler','method','query_string','request_url','response_mime_type') as $m) {
				$val = Dase_Registry::get($m) ? htmlspecialchars(Dase_Registry::get($m)) : ' -- ';
				$d_atts->addChild($m,$val);
			}
			$routes_xml = $sx->addChild('routes');
			$routes = Dase_Routes::compile();
			$method = strtolower($_SERVER['REQUEST_METHOD']);
			foreach ($routes[$method] as $regex => $parts) {
				$route = $routes_xml->addChild('route');
				$route->addChild('regex',$regex);
				if (is_array($parts)) {
					foreach ($parts as $k => $v) {
						if (!$v) { $v = "--"; }
						if ('end' != $k) {
							$route->addChild($k,$v);
						}
					}
				}
			}
			$method = Dase_Registry::get('method');
			if (($method != 'get') && ($method != 'post')) {
				//send back plain text debug msg for put & delete
				$t->stylesheet = XSLT_PATH.'error/debug_text.xsl';
				$t->source = XSLT_PATH.'error/layout_text.xml';
				header("Content-Type: text/plain; charset=utf-8");
			} else {
				$t->stylesheet = XSLT_PATH.'error/debug.xsl';
				$t->source = XSLT_PATH.'error/layout.xml';
			}
			$t->addSourceNode($sx);
		} else {
			$t->stylesheet = XSLT_PATH.'error/production.xsl';
		}
		$t->set('msg',$msg);
		echo $t->transform();
		exit;
	}
}

