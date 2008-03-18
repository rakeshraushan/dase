<?php

class AtomHandler
{
	public static function original($params)
	{
		require_once(DASE_PATH . '/lib/atomlib.php');
		$parser = new AtomParser();
		$parser->parse();
		$parsed = array_pop($parser->feed->entries);
		$post_title = $parsed->title[1];
		$post_content = $parsed->content[1];
		Dase::log('standard',$post_title . "\n" . $post_content);
		echo "success!";
	}

	function get_service($params)
	{
		$c = Dase_Collection::get($params);
		$entries_url = APP_ROOT . '/collection/' . $c->ascii_id . '/posts'; 
		//$entries_url = APP_ROOT . '/collection/' . $c->ascii_id . '/attachments'; 
		$media_url = APP_ROOT . '/collection/' . $c->ascii_id . '/attachments'; 
		$categories_url = APP_ROOT . '/collection/' . $c->ascii_id . '/categories';;
		$service_doc = <<<EOD
<service xmlns="http://www.w3.org/2007/app" xmlns:atom="http://www.w3.org/2005/Atom">
  <workspace>
	<atom:title>$c->collection_name Workspace</atom:title>
	<collection href="$entries_url">
	  <atom:title>$c->collection_name Posts</atom:title>
	  <accept>application/atom+xml;type=entry</accept>
	  <categories href="$categories_url" />
	</collection>
	<collection href="$media_url">
	  <atom:title>$c->collection_name Media</atom:title>
		<accept>image/*</accept>
		<accept>audio/*</accept>
		<accept>video/*</accept>
	</collection>
  </workspace>
</service>
EOD;
		Dase::display($service_doc,true);
	}
















}

