<?php

class ManageHandler
{
	public static function checkRoutes($params)
	{
		$r = Dase::compileRoutes();
		foreach ($r as $method => $route) {
			foreach ($route as $match => $parts) {
				if (isset($parts['prefix'])) {
					//modules, by convention, have one handler in a file named
					//'handler.php' with classname {Module}ModuleHandler
					@include_once(DASE_PATH . $parts['prefix'] . '/handler.php');
					$classname = ucfirst($parts['name']) . 'ModuleHandler';
				} else {
					@include_once(DASE_PATH .  '/handlers/' . $parts['handler'] . '.php');
					$classname = ucfirst($parts['handler']) . 'Handler';
				}
				if(method_exists($classname,$parts['action'])) {
				} else {
					print "<pre>";
					print_r($parts);
					print "</pre>";
				}
			}
		}
	}

	public static function index($params)
	{
		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'manage/common.xsl';
		$t->source = XSLT_PATH.'manage/source.xml';
		Dase::display($t->transform());
	}

	public static function viewLog($params)
	{
		$params = Dase_Registry::get('params');
		if (isset($params['log_name'])) {
			switch ($params['log_name']) {
			case 'standard':
				$file = 'standard.log';
				break;
			case 'error':
				$file = 'error.log';
				break;
			case 'sql':
				$file = 'sql.log';
				break;
			case 'remote':
				$file = 'remote.log';
				break;
			default:
				header("HTTP/1.0 404 Not Found");
				exit;
			}
			$log = file_get_contents(DASE_PATH . "/log/" . $file);
		}
		$tpl = new Smarty;
		$tpl->assign('app_root',APP_ROOT);
		$tpl->assign('breadcrumb_url',"manage/log/{$params['log_name']}");
		$tpl->assign('breadcrumb_name',"{$params['log_name']} log");
		$tpl->assign('log',$log);
		$tpl->assign('log_name',$params['log_name']);
		$tpl->display('manage/index.tpl');
		exit;
	}

	public static function phpinfo($params)
	{
		phpinfo();
		exit;
	}

	public static function stats($params)
	{
		$tag_item = new Dase_DBO_TagItem;
		$tot = array();
		$slice = array();
		$top_ten = array();
		foreach ($tag_item->find() as $tag_it) {
			if (isset($tot[$tag_it->p_collection_ascii_id])) {
				$tot[$tag_it->p_collection_ascii_id]++;
			} else {
				$tot[$tag_it->p_collection_ascii_id] = 1;
			}
		}
		arsort($tot);
		$slice = array_slice($tot,0,20);
		foreach ($slice as $k => $v) {
			$coll = Dase_DBO_Collection::get($k);
			if ($coll) {
				$top_ten[$coll->collection_name] = $v;
			}
		}
		$db = Dase_DB::get();
		$sql = "
			SELECT count(item.id), collection_name
			FROM item, collection
			WHERE item.collection_id = collection.id
			GROUP BY collection_name
			ORDER BY count DESC
			LIMIT 10
			";
		$sth = $db->query($sql);
		$sth->setFetchMode(PDO::FETCH_ASSOC);
		$by_size = array();
		while ($row = $sth->fetch()) {
			$by_size[$row['collection_name']] = $row['count'];
		}
		print "<pre>";
		print_r($top_ten);
		print_r($by_size);
		print "</pre>";
		exit;
	}

	public static function exec($params)
	{
	}

	public static function modules($params)
	{
		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'manage/modules.xsl';
		$t->source = XSLT_PATH.'manage/source.xml';
		$sx = new SimpleXMLElement('<modules/>');

		$dir = (DASE_PATH . "/modules");
		foreach (new DirectoryIterator($dir) as $file) {
			if ($file->isDir() && !$file->isDot()) {
				$module = $file->getFilename();
				$mod = $sx->addChild('module',$module);
				$mod->addAttribute('installed','installed');
			}
		}

		$t->addSourceNode($sx);
		Dase::display($t->transform());
	}

	public static function routes($params)
	{
		$t = new Dase_Xslt;
		$t->stylesheet = XSLT_PATH.'manage/routes.xsl';
		$t->source = XSLT_PATH.'manage/source.xml';
		$routes = Dase::compileRoutes();
		$sx = new SimpleXMLElement('<routes/>');
		foreach ($routes as $http_method => $routes_set) {
			$method = $sx->addChild('h3',$http_method . ' method');
			$dl = $sx->addChild('dl');
			$dl->addAttribute('class','routes');
			foreach($routes_set as $match => $atts) {
				$dl->addChild('dt',$match);
				if (is_array($atts)){
					foreach($atts as $name => $value) {
						$dl->addChild('dd',"[$name] $value");
					}
				}
			}
		}
		$t->addSourceNode($sx);
		Dase::display($t->transform());
	}



}

