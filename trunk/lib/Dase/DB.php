<?php

class Dase_DB {

	// Internal variable to hold the connection
	private static $db;
	private static $name;
	private static $type;
	// No cloning or instantiating allowed
	final private function __construct() { }
	final private function __clone() { }

	public static function get($dbname = null) {
		// Connect if not already connected
		if (is_null(self::$db)) {
			include (DASE_CONFIG); 
			self::$type = $conf['db_type'];
			if ($dbname) {
				self::$name = $dbname;
			} else {
				self::$name = $conf['db_name'];
			}
			$host = $conf['db_host'];
			$user = $conf['db_user'];
			$pass = $conf['db_pass'];

			if ('sqlite' == self::$type) {
				$dsn = "sqlite:" . DASE_PATH . '/sqlite/dase.db';
				self::$db = new PDO($dsn);
			} else {
				$dsn = self::$type . ":host=$host;dbname=" . self::$name;
				$driverOpts = array();
				self::$db = new PDO($dsn, $user, $pass, $driverOpts);
			}
		}
		// Return the connection
		return self::$db;
	}

	public static function getDbName() {
		self::get();
		return self::$name;
	}

	public static function setDbName($dbname) {
		self::get($dbname);
	}

	public static function getDbType() {
		self::get();
		return self::$type;
	}

	public static function listTables() {
		$db = self::get();
		if ('mysql' == self::$type) {
			$sql = "SHOW TABLES";
		}
		//from Zend Db Adapter
		if ('pgsql' == self::$type) {
			$sql = "SELECT c.relname AS table_name "
				. "FROM pg_class c, pg_user u "
				. "WHERE c.relowner = u.usesysid AND c.relkind = 'r' "
				. "AND NOT EXISTS (SELECT 1 FROM pg_views WHERE viewname = c.relname) "
				. "AND c.relname !~ '^(pg_|sql_)' "
				. "UNION "
				. "SELECT c.relname AS table_name "
				. "FROM pg_class c "
				. "WHERE c.relkind = 'r' "
				. "AND NOT EXISTS (SELECT 1 FROM pg_views WHERE viewname = c.relname) "
				. "AND NOT EXISTS (SELECT 1 FROM pg_user WHERE usesysid = c.relowner) "
				. "AND c.relname !~ '^pg_'";
		}
		$sth = $db->prepare($sql);
		$sth->execute();
		return ($sth->fetchAll(PDO::FETCH_COLUMN));
	}	

	public static function listColumns($table) {
		$db = self::get();
		if ('mysql' == self::$type) {
			$sql = "SHOW FIELDS FROM $table";
		}
		if ('pgsql' == self::$type) {
			$sql = "SELECT attname FROM pg_class, pg_attribute WHERE 
				pg_class.relname = '$table' AND pg_class.oid = pg_attribute.attrelid AND 
				pg_attribute.attnum > 0  
				AND attname NOT LIKE '....%'
				ORDER BY attname";
		}
		$sth = $db->prepare($sql);
		$sth->execute();
		return ($sth->fetchAll(PDO::FETCH_COLUMN));
	}	

	public static function getMetadata($table) {
		//fix this so it works w/ sqlite
		$db = self::get();
		$sql = "SELECT column_name, data_type, character_maximum_length 
			FROM information_schema.columns 
			WHERE table_name = '$table'";
		$sth = $db->prepare($sql);
		$sth->execute();
		return ($sth->fetchAll(PDO::FETCH_ASSOC));
	}	

	public static function getSchemaXml() {
		$writer = new XMLWriter();
		$writer->openMemory();
		$writer->setIndent(true);
		$writer->startDocument('1.0','UTF-8');
		$writer->startElement('database');
		$writer->writeAttribute('name',Dase_DB::getDbName());
		foreach (Dase_DB::listTables() as $table) {
			$writer->startElement('table');
			$writer->writeAttribute('name',$table);
			foreach (Dase_DB::getMetadata($table) as $col) {
				$writer->startElement('column');
				$writer->writeAttribute('name',$col['column_name']);
				$writer->writeAttribute('type',$col['data_type']);
				if ('id' == $col['column_name']) {
					$writer->writeAttribute('is_primary_key','true');
				}
				if ($col['character_maximum_length']) {
					$writer->writeAttribute('max_length',$col['character_maximum_length']);
				}
				$writer->endElement();
			}
			$writer->endElement();
		}
		$writer->endElement();
		$writer->endDocument();
		return $writer->flush(true);
	}	
}
