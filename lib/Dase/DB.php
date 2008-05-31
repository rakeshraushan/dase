<?php
/*
 * Copyright 2008 The University of Texas at Austin
 *
 * This file is part of DASe.
 * 
 * DASe is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * DASe is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with DASe.  If not, see <http://www.gnu.org/licenses/>.
 */ 

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
			$conf = Dase::getConfig('db'); 
			self::$type = $conf['type'];
			if ($dbname) {
				self::$name = $dbname;
			} else {
				self::$name = $conf['name'];
			}
			$host = $conf['host'];
			$sqlite_path = $conf['path'];
			$user = $conf['user'];
			$pass = $conf['pass'];
			$driverOpts = array();
			if ('sqlite' == self::$type) {
				$dsn = "sqlite:$sqlite_path";
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
