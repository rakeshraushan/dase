<?php

class Dase_Handler {

	protected $request;

	public function dispatch($request)
	{
		foreach ($this->resource_map as $uri_template => $resource) {
			//first, translate resource map uri template to a regex
			$uri_template = trim($request->handler.'/'.$uri_template,'/');
			$uri_regex = $uri_template;

			//skip regex template stuff if uri_template is a plain string
			if (false !== strpos($uri_template,'{')) {
				//stash param names into $template_matches
				$num = preg_match_all("/{([\w]*)}/",$uri_template,$template_matches);
				if ($num) {
					$uri_regex = preg_replace("/{[\w]*}/","([\w]*)",$uri_template);
				}
			}

			//second, see if it matches the request uri (a.k.a. path)
			if (preg_match("!^$uri_regex\$!",$request->path,$uri_matches)) {

				//create parameters based on uri template and request matches
				if (isset($template_matches[1]) && isset($uri_matches[1])) { 
					array_shift($uri_matches);
					$params = array_combine($template_matches[1],$uri_matches);
					$request->setParams($params);
				}

				//given the method, resource, and format, try and call proper method
				if ('html' == $request->format) {
					//html is default
					$method = $request->method.ucfirst($resource);
				} else {
					$method = $request->method.ucfirst($resource).ucfirst($request->format);
				}
				if (method_exists($this,$method)) {
					$this->setup($request);
					$this->{$method}($request);
				} else {
					print $method;
					Dase::error(404);
				}
			}
		}
		Dase::error(404);
	}

	public function setup($request)
	{
		return;
	}
			
	public function checkCache($request,$ttl=null)
	{
		$cache = Dase_Cache::get($request);
		$content = $cache->getData($ttl);
		if ($content) {
			Dase::display($content,$request,false);
		}
	}
}

