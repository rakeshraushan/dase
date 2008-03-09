<?php

require_once 'Dase/DB.php';

class Dase_DB_Object implements IteratorAggregate
{
	public $id = 0;
	public $sql;
	public $bind = array();
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
		if ( array_key_exists( $key, $this->fields ) ) {
			return $this->fields[ $key ];
		}
	}

	function __set( $key, $value ) {
		if ( array_key_exists( $key, $this->fields ) ) {
			$this->fields[ $key ] = $value;
			return true;
		}
		return false;
	}

	function hasMember($key) {
		if ( array_key_exists( $key, $this->fields ) ) {
			return true;
		} else {
			return false;
		}
	}

	function setLimit($limit) {
		$this->limit = $limit;
	}

	function orderBy($ob) {
		$this->order_by = $ob;
	}

	function addWhere($field,$value,$operator) {
		if ( 
			array_key_exists( $field, $this->fields) &&
			in_array($operator,array('is','like','=','!=','<','>'))
		) {
			$this->qualifiers[] = array(
				'field' => $field,
				'value' => $value,
				'operator' => $operator
			);
		} else {
			throw new Exception('addWhere problem');
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
			print "DASE_DB_Object 'load()' Problem ({$error[2]})";
		}
		$sth->setFetchMode(PDO::FETCH_INTO, $this);
		$sth->execute(array( ':id' => $this->id));
		if ($sth->fetch()) {
			return $this;
		} else {
			return false;
		}
	}

	function insert($seq = '') { //postgres needs id specified
		if ('pgsql' == Dase_DB::getDbType()) {
			if (!$seq) {
				//beware!!! fix this after no longer using DB_DataObject
				//$seq = $this->table . '_id_seq';
				$seq = $this->table . '_seq';
			}
			//$id = "nextval('$seq'::text)";
			$id = "nextval(('public.$seq'::text)::regclass)"; 	
		} elseif ('sqlite' == Dase_DB::getDbType()) {
			$id = 'null';
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
			throw new Exception("problem on insert: " . $error[2]);
			exit;
		}
		if ($sth->execute($bind)) {
			$last_id = $db->lastInsertId($seq);
			$this->id = $last_id;
			if (defined('DEBUG')) {
				Dase::log('sql',"$sql /// last insert id = $last_id");
			}
			return $last_id;
		} else { 
			$error = $sth->errorInfo();
			throw new Exception("could not insert: " . $error[2]);
		}
	}

	function getMethods() {
		$class = new ReflectionClass(get_class($this));
		return $class->getMethods();
	}

	function findOne() {
		$this->setLimit(1);
		return $this->find()->fetch();
	}

	function find() {
		//finds matches based on set fields (omitting 'id')
		//returns an iterator
		$db = Dase_DB::get();
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
				$sets[] = "$f $op $v";
			}
		}
		$where = join( " AND ", $sets );
		if ($where) {
			$sql = "SELECT * FROM ".$this->table. " WHERE ".$where;
		} else {
			$sql = "SELECT * FROM ".$this->table;
		}
		if (isset($this->order_by)) {
			$sql .= " ORDER BY $this->order_by";
		}
		if (isset($this->limit)) {
			$sql .= " LIMIT $this->limit";
		}
		$this->sql = $sql;
		$this->bind = $bind;
		$sth = $db->prepare( $sql );
		if (defined('DEBUG')) {
			Dase::log('sql',$sql . ' /// ' . join(',',$bind));
		}
		$sth->setFetchMode(PDO::FETCH_INTO,$this);
		$sth->execute($bind);
		//NOTE: PDOStatement implements Traversable. 
		//That means you can use it in foreach loops 
		//to iterate over rows:
		// foreach ($thing->find() as $one) {
		//     print_r($one);
		// }
		return $sth;
	}

	public static function query($sql) {
		//return generic object
		$db = Dase_DB::get();
		$sth = $db->prepare($sql);
		$sth->setFetchMode(PDO::FETCH_OBJ);
		$sth->execute();
		return $sth;
	}

	function update() {
		$db = Dase_DB::get();
		foreach( $this->fields as $key => $val) {
			if ('timestamp' != $key || $val) { //prevents null timestamp as update
				$fields[]= $key." = ?";
				$values[]= $val;
			}
		}
		$set = join( ",", $fields );
		$sql = "UPDATE {$this->{'table'}} SET $set WHERE id=?";
		$values[] = $this->id;
		$sth = $db->prepare( $sql );
		if (defined('DEBUG')) {
			Dase::log('sql',$sql . ' /// ' . join(',',$values));
		}
		if (!$sth->execute($values)) {
			return $sth->errorInfo();
		}
	}

	function delete() {
		$db = Dase_DB::get();
		$sth = $db->prepare(
			'DELETE FROM '.$this->table.' WHERE id=:id'
		);
		if (defined('DEBUG')) {
			Dase::log('sql',"deleting id $this->id from $this->table table");
		}
		return $sth->execute(array( ':id' => $this->id));
		//probably need to destroy $this here
	}

	//implement SPL IteratorAggregate:
	//now simply use 'foreach' to iterate 
	//over object properties
	public function getIterator() {
		return new ArrayObject($this->fields);
	}

}
