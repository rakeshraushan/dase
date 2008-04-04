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
		Dase_Log::put('standard',$post_title . "\n" . $post_content);
		echo "success!";
	}

}

