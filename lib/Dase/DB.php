<?php

class Dase_DB {

	// Internal variable to hold the connection
	private static $db;
	private static $name;
	private static $type;
	// No cloning or instantiating allowed
	final private function __construct()
	{ }
	final private function __clone()
	{ }

	public static function get($dbname = null)
	{
		// Connect if not already connected
		if (is_null(self::$db)) {
			$conf = Dase_Config::get('db'); 
			self::$type = $conf['type'];
			if ($dbname) {
				self::$name = $dbname;
			} else {
				self::$name = $conf['name'];
			}
			$host = $conf['host'];
			$user = $conf['user'];
			$pass = $conf['pass'];
			$driverOpts = array();
			if ('sqlite' == self::$type) {
				$dsn = "sqlite:".$conf['path'];
			} else {
				$dsn = self::$type . ":host=$host;dbname=" . self::$name;
			}
			try {
				self::$db = new PDO($dsn, $user, $pass, $driverOpts);
				if ('mysql' == self::$type) {
					self::$db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
				}
			} catch (PDOException $e) {
				echo 'connect failed: ' . $e->getMessage();
			}
		}
		// Return the connection
		return self::$db;
	}

	public static function logger($sth,$params=array(),$bool,$msg='DB query') {
	}

	public static function getDbName()
	{
		self::get();
		return self::$name;
	}

	public static function setDbName($dbname)
	{
		self::get($dbname);
	}

	public static function getDbType()
	{
		self::get();
		return self::$type;
	}

	public static function listIndexes()
	{
		$db = self::get();
		if ('mysql' == self::$type) {
			return "under construction";	
		}
		if ('pgsql' == self::$type) {
			$sql =<<< EOF
			SELECT c.relname as "name", 
				CASE c.relkind WHEN 'r' THEN 'table' WHEN 'v' THEN 'view' WHEN 'i' THEN 'index' WHEN 'S' THEN 'sequence' WHEN 's' THEN 'special' END as "type",
				c2.relname as "table"
				FROM pg_catalog.pg_class c
				JOIN pg_catalog.pg_index i ON i.indexrelid = c.oid
				JOIN pg_catalog.pg_class c2 ON i.indrelid = c2.oid
				LEFT JOIN pg_catalog.pg_user u ON u.usesysid = c.relowner
				LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
				WHERE c.relkind IN ('i','')
				AND n.nspname NOT IN ('pg_catalog', 'pg_toast')
				AND pg_catalog.pg_table_is_visible(c.oid)
				ORDER BY 1,2;
EOF;
		}
		if ('sqlite' == self::$type) {
			return "under construction";	
		}
		$sth = $db->prepare($sql);
		$sth->setFetchMode(PDO::FETCH_ASSOC);
		$sth->execute();
		return ($sth->fetchAll());
	}	

	public static function listTables()
	{
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
		if ('sqlite' == self::$type) {
			$sql = "
				SELECT name FROM sqlite_master
				WHERE type='table'
				ORDER BY name
				";
		}
		$sth = $db->prepare($sql);
		$sth->execute();
		return ($sth->fetchAll(PDO::FETCH_COLUMN));
	}	

	public static function listColumns($table)
	{
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
		if ('sqlite' == self::$type) {
			$sql = "PRAGMA table_info($table)";
			$sth = $db->prepare($sql);
			$sth->execute();
			while ($row = $sth->fetch()) {
				$names[] = $row['name'];
				//$type = $row['type'];
			}
			return $names;
		}
		$sth = $db->prepare($sql);
		$sth->execute();
		return ($sth->fetchAll(PDO::FETCH_COLUMN));
	}	

	public static function getMetadata($table)
	{
		$db = self::get();
		if ('sqlite' == self::$type) {
			$sql = "PRAGMA table_info($table)";
			$sth = $db->prepare($sql);
			$sth->execute();
			while ($row = $sth->fetch()) {
				$col = array();
				$col['column_name'] = $row['name'];
				if (strpos($row['type'],'(')) {
					$col['data_type'] = substr($row['type'],0,strpos($row['type'],'('));
					if ('varchar' == $col['data_type']) {
						preg_match('/\d+/',$row['type'],$matches);
						$col['character_maximum_length'] = $matches[0];
					}
				} else{
					$col['data_type'] = $row['type'];
				}
				$result[] = $col;
			}
			return $result;
		}
		$sql = "SELECT column_name, data_type, character_maximum_length, is_nullable,column_default
			FROM information_schema.columns 
			WHERE table_name = '$table'";
		$sth = $db->prepare($sql);
		$sth->execute();
		return ($sth->fetchAll(PDO::FETCH_ASSOC));
	}	

	public static function getSchemaXml()
	{
		$writer = new XMLWriter();
		$writer->openMemory();
		$writer->setIndent(true);
		$writer->startDocument('1.0','UTF-8','yes');
		$writer->startElement('database');
		$writer->writeAttribute('name',Dase_DB::getDbName());
		foreach (Dase_DB::listTables() as $table) {
			$writer->startElement('table');
			$writer->writeAttribute('name',$table);
			foreach (Dase_DB::getMetadata($table) as $col) {
				$writer->startElement('column');
				$writer->writeAttribute('name',$col['column_name']);
				$writer->writeAttribute('type',$col['data_type']);
				$writer->writeAttribute('null',$col['is_nullable']);
				if (false === strpos($col['column_default'],'nextval')) {
					$writer->writeAttribute('default',$col['column_default']);
				}
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
