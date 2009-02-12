<?php

class Dase_Handler {

	protected $request;

	public function dispatch($request)
	{
		//if it is a module subclass, append the module resource map
		if (isset($this->module_resource_map)) {
			$this->resource_map = array_merge($this->resource_map,$this->module_resource_map);
		}

		foreach ($this->resource_map as $uri_template => $resource) {
			//first, translate resource map uri template to a regex
			$uri_template = trim($request->handler.'/'.$uri_template,'/');
			$uri_regex = $uri_template;

			//skip regex template stuff if uri_template is a plain string
			if (false !== strpos($uri_template,'{')) {
				//stash param names into $template_matches
				$num = preg_match_all("/{([\w]*)}/",$uri_template,$template_matches);
				if ($num) {
					$uri_regex = preg_replace("/{[\w]*}/","([\w-,.]*)",$uri_template);
				}
			}

			//Dase_Log::debug(" (regex) $uri_regex | (path) $request->path [resource: $resource]");
			//second, see if uri_regex matches the request uri (a.k.a. path)
			if (preg_match("!^$uri_regex\$!",$request->path,$uri_matches)) {
				Dase_Log::debug("matched resource $resource");
				//create parameters based on uri template and request matches
				if (isset($template_matches[1]) && isset($uri_matches[1])) { 
					array_shift($uri_matches);
					$params = array_combine($template_matches[1],$uri_matches);
					$request->setParams($params);
				}
				$method = $this->determineMethod($resource,$request);
				Dase_Log::debug("try method $method");
				if (method_exists($this,$method)) {
					$request->resource = $resource;
					$this->setup($request);
					$this->{$method}($request);
				} else {
					$request->renderError(404,'no handler method');
				}
			}
		}
		$request->renderError(404,'no such resource');
	}

	protected function determineMethod($resource,$request)
	{
		if ('post' == $request->method) {
			$method = 'postTo';
		} else {
			$method = $request->method;
		}
		if (('html'==$request->format) || ('get' != $request->method)) {
			$format = '';
		} else {
			$format = ucfirst($request->format);
		}
		//camel case
		$resource = Dase_Util::camelize($resource);

		$handler_method = $method.$resource.$format;
		return $handler_method;
	}

	protected function setup($request)
	{
		return;
	}
			
}

