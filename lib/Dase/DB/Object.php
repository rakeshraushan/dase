<?php

require_once 'Dase/DB.php';

class Dase_DB_Object {
	public $id = 0;
	private $table;
	private $fields = array();
	protected $limit;
	protected $order_by;
	protected $qualifiers = array();

	function __construct( $table, $fields ) {
		$this->table = $table;
		foreach( $fields as $key ) {
			$this->fields[ $key ] = null;
		}
	}

	function __get( $key ) {
		return $this->fields[ $key ];
	}

	function __set( $key, $value ) {
		if ( array_key_exists( $key, $this->fields ) ) {
			$this->fields[ $key ] = $value;
			return true;
		}
		return false;
	}

	function setLimit($limit) {
		$this->limit = $limit;
	}

	function orderBy($ob) {
		$this->order_by = $ob;
	}

	function addWhere($field,$value,$operator) {
		if ( array_key_exists( $field, $this->fields)) {
			//should filter for valid operator as well
			$this->qualifiers[] = array(
					'field' => $field,
					'value' => $value,
					'operator' => $operator
					);
		}
	}

	function __toString() {
		$members = '';
		$table = $this->table;
		$id = $this->id;
		foreach ($this->fields as $key => $value) {
			$members .= "$key: $value\n";
		}
		$out = "--$table ($id)--\n$members\n";
		return $out;
	}

	function load( $id ) {
		$this->id = $id;
		$db = Dase_DB::get();
		$table = $this->table;
		$sql = "SELECT * FROM $table WHERE id=:id";
		$sth = $db->prepare($sql);
		if (! $sth) {
			$error = $db->errorInfo();
			print "Load Problem ({$error[2]})";
		}
		$sth->setFetchMode(PDO::FETCH_INTO, $this);
		$sth->execute(array( ':id' => $this->id));
		$sth->fetch();
	}

	function insert($seq = '') { //postgres need id specified
		if ('pgsql' == Dase_DB::getDbType()) {
			if (!$seq) {
				$seq = $this->table . '_id_seq';
			}
			$id = "nextval('$seq'::text)";
		} else {
			$id = 0;
		}
		$db = Dase_DB::get();
		$fields = array('id');
		$inserts = array($id);
		foreach( array_keys( $this->fields ) as $field )
		{
			$fields []= $field;
			$inserts []= ":$field";
			$bind[":$field"] = $this->fields[ $field ];
		}
		$field_set = join( ", ", $fields );
		$insert = join( ", ", $inserts );
		$sql = "INSERT INTO ".$this->table. 
			" ( $field_set ) VALUES ( $insert )";
		$sth = $db->prepare( $sql );
		if (! $sth) {
			$error = $db->errorInfo();
			print "Problem ({$error[2]})";
			exit;
		}
		if ($sth->execute($bind)) {
			$last_id = $db->lastInsertId($seq);
			$this->id = $last_id;
			return $last_id;
		} else { 
			$msg_array = $sth->errorInfo();
			throw new Exception("could not insert: " . $msg_array[2]);
		}
	}

	function query($sql,$params) {
		$db = Dase_DB::get();
		$class = get_class($this);
		$objects = array();
		$sth = $db->prepare( $sql );
		$sth->setFetchMode(PDO::FETCH_ASSOC);
		$sth->execute($params);
		while ($row = $sth->fetch()){
			$obj = new $class($row);
			$obj->fields['id'] = $obj->id;
			$objects[] = $obj;
		}
		if (count($objects)) {
			return $objects;
		} else {
			return false;
		}
	}

	function getMethods() {
		$class = new ReflectionClass(get_class($this));
		return $class->getMethods();
	}

	function find($get_one = false) {
		//finds matches based on set fields (omitting 'id')
		$db = Dase_DB::get();
		$class = get_class($this);
		$objects = array();
		$sets = array();
		$bind = array();
		$limit = '';
		foreach( array_keys( $this->fields ) as $field ) {
			if (isset($this->fields[ $field ]) 
					&& ('id' != $field)) {
				$sets []= "$field = :$field";
				$bind[":$field"] = $this->fields[ $field ];
			}
		}
		if (isset($this->qualifiers)) {
			//work on this
			foreach ($this->qualifiers as $qual) {
				$f = $qual['field'];
				$op = $qual['operator'];
				$v = $db->quote($qual['value']);
				$sets [] = "$f $op $v";
			}
		}
		$where = join( " AND ", $sets );
		$sql = "SELECT * FROM ".$this->table. " WHERE ".$where;
		if (isset($this->order_by)) {
			$sql .= " ORDER BY $this->order_by";
		}
		if (isset($this->limit)) {
			$sql .= " LIMIT $this->limit";
		}
		$sth = $db->prepare( $sql );
		if (defined('DEBUG')) {
			Dase_Log::sql($sql . ' /// ' . join(',',$bind));
		}
		$sth->setFetchMode(PDO::FETCH_ASSOC);
		$sth->execute($bind);
		while ($row = $sth->fetch()){
			$obj = new $class($row);
			$obj->fields['id'] = $obj->id;
			$objects[] = $obj;
		}
		if (count($objects)) {
			if ($get_one) {
				$found = $objects[0];
				$this->id = $found->id;
				$this->fields['id'] = $found->id; //do we need this????
				foreach( array_keys( $found->fields ) as $field ) {
					if (isset($found->fields[ $field ])) {
						$this->$field = $found->$field;
					}
				}
				return $found;
			} else {
				return $objects;
			}
		} else {
			return $objects; //empty array, should evaluate to false
		}
	}

	function update($assoc,$val = null) {
		//will accept a key,val pair OR assciative array of keyvals
		if (!is_array($assoc)) {
			$col = $assoc;
			$assoc = array($col => $val);
		}
		$db = Dase_DB::get();
		$sets = array();
		foreach( $assoc as $col => $val)
		{
			$sets[] = "$col = :$col";
			$bind[":$col"] = $val;
		}
		$set = join( ", ", $sets );
		$sql = 'UPDATE '.$this->table.' SET '.$set.
			" WHERE id=:id";
		$sth = $db->prepare( $sql );
		foreach ($bind as $k => $v) {
			$type = $this->_getType($v);
			$sth->bindParam($k,$v,$type);
		}
		$sth->bindParam(':id',$this->id);
		if (!$sth->execute()) {
			return $sth->errorInfo();
		}
	}

	function delete() {
		$db = Dase_DB::get();
		$sth = $db->prepare(
				'DELETE FROM '.$this->table.' WHERE id=:id'
				);
		$sth->execute(array( ':id' => $this->id));
		//probably need to destroy $this here
	}

	function deleteAll() {
		$db = Dase_DB::get();
		$sth = $db->prepare( 'DELETE FROM '.$this->table );
		$sth->execute();
	}

	function getAll() {
		$db = Dase_DB::get();
		$class = get_class($this);
		$objects = array();
		$sql = "SELECT * FROM ".$this->table;
		if (isset($this->order_by)) {
			$sql .= " ORDER BY $this->order_by";
		}
		if (isset($this->limit)) {
			$sql .= " LIMIT $this->limit";
		}
		$sth = $db->prepare( $sql );
		$sth->setFetchMode(PDO::FETCH_ASSOC);
		$sth->execute();
		while ($row = $sth->fetch()){
			$obj = new $class($row);
			$obj->fields['id'] = $obj->id;
			$objects[] = $obj;
		}
		return $objects;
	}

	//from http://framework.zend.com/issues/secure/attachment/10145/db_explicit_bind.patch
	protected function _getType($value)
	{
		if (is_bool($value)) {
			$type = PDO::PARAM_BOOL;
		} else if (is_null($value)) {
			$type = PDO::PARAM_NULL;
		} else if (is_integer($value)) {
			$type = PDO::PARAM_INT;
		} else {
			$type = PDO::PARAM_STR;
		}
		return $type;
	}
}
