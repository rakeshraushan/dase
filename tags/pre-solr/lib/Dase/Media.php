<?php
Class Dase_Media 
{
	public static $media_types = array(
		'application/pdf',
		'audio/*',
		'image/*',
		'text/*',
		'video/*',
	);

	function __construct() {}

	/** 
	 * from php port of Mimeparse
	 * Python code (http://code.google.com/p/mimeparse/)
	 * @author Joe Gregario, Andrew "Venom" K.
	 */
	public static function parseMimeType($mime_type)
	{
		$parts = split(";", $mime_type);
		$params = array();
		foreach ($parts as $i=>$param) {
			if (strpos($param, '=') !== false) {
				list ($k, $v) = explode('=', trim($param));
				$params[$k] = $v;
			}
		}
		list ($type, $subtype) = explode('/', $parts[0]);
		if (!$subtype) throw new Exception("malformed mime type");
		return array(trim($type), trim($subtype), $params);
	}

	public static function getAcceptedTypes()
	{
		return self::$media_types;
	}

	/** returns type on success, false on failure */
	public static function isAcceptable($content_type)
	{
		$ok_type = false;
		try {
			list($type,$subtype) = Dase_Media::parseMimeType($content_type);
		} catch (Exception $e) {
			return false;
		}
		foreach(Dase_Media::getAcceptedTypes() as $t) {
			list($acceptedType,$acceptedSubtype) = explode('/',$t);
			if($acceptedType == '*' || $acceptedType == $type) {
				if($acceptedSubtype == '*' || $acceptedSubtype == $subtype)
					$ok_type = $type . "/" . $subtype;
			}
		}
		return $ok_type;
	}
}
