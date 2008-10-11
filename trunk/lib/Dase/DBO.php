<?php
require_once 'Dase/DB.php';

/* this class implements the ActiveRecord pattern
 */

class Dase_DBO_Exception extends Exception {}

class Dase_DBO implements IteratorAggregate
{
	public $id = 0;
	public $sql;
	public $bind = array();
	private $table;
	private $fields = array(); 
	protected $limit;
	protected $order_by;
	protected $qualifiers = array();

	function __construct( $table, $fields )
	{
		$this->table = Dase_Config::get('table_prefix').$table;
		foreach( $fields as $key ) {
			$this->fields[ $key ] = null;
		}
	}

	public function getTable()
	{
		return $this->table;
	}

	function __get( $key )
	{
		if ( array_key_exists( $key, $this->fields ) ) {
			return $this->fields[ $key ];
		}
		//automatically call accessor method is it exists
		$classname = get_class($this);
		$method = 'get'.ucfirst($key);
		if (method_exists($classname,$method)) {
			return $this->{$method}();
		}	
	}

	function __set( $key, $value )
	{
		if ( array_key_exists( $key, $this->fields ) ) {
			$this->fields[ $key ] = $value;
			return true;
		}
		return false;
	}

	private function _dbGet() {
		try {
			return Dase_DB::get();
		} catch (PDOException $e) {
			throw new PDOException($e->getMessage());
		}
	}

	function getFieldNames() {
		return array_keys($this->fields);
	}

	function hasMember($key)
	{
		if ( array_key_exists( $key, $this->fields ) ) {
			return true;
		} else {
			return false;
		}
	}

	function setLimit($limit)
	{
		$this->limit = $limit;
	}

	function orderBy($ob)
	{
		$this->order_by = $ob;
	}

	function addWhere($field,$value,$operator)
	{
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
			throw new Dase_DBO_Exception('addWhere problem');
		}
	}

	function __toString()
	{
		$members = '';
		$table = $this->table;
		$id = $this->id;
		foreach ($this->fields as $key => $value) {
			$members .= "$key: $value\n";
		}
		$out = "--$table ($id)--\n$members\n";
		return $out;
	}

	function load( $id )
	{
		if (!$id) {return false;}
		$this->id = $id;
		$db = $this->_dbGet();
		$table = $this->table;
		$sql = "SELECT * FROM $table WHERE id=:id";
		$sth = $db->prepare($sql);
		if (! $sth) {
			$errs = $db->errorInfo();
			if (isset($errs[2])) {
				throw new Dase_DBO_Exception($errs[2]);
			}
		}
		Dase_Log::debug($sql . ' /// '.$id);
		$sth->setFetchMode(PDO::FETCH_INTO, $this);
		$sth->execute(array( ':id' => $this->id));
		if ($sth->fetch()) {
			return $this;
		} else {
			return false;
		}
	}

	function insert($seq = '')
	{ //postgres needs id specified
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
		$db = $this->_dbGet();
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
		//$this->table string is NOT tainted
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
			Dase_Log::debug($sql." /// last insert id = $last_id");
			return $last_id;
		} else { 
			$error = $sth->errorInfo();
			throw new Exception("could not insert: " . $error[2]);
		}
	}

	function getMethods()
	{
		$class = new ReflectionClass(get_class($this));
		return $class->getMethods();
	}

	function findOne()
	{
		$this->setLimit(1);
		$set = $this->find()->fetchAll();
		if (count($set)) {
			return $set[0];
		}
	}

	function find()
	{
		//finds matches based on set fields (omitting 'id')
		//returns an iterator
		$db = $this->_dbGet();
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
				//allows is to add 'is null' qualifier
				if ('null' == $qual['value']) {
					$v = $qual['value'];
				} else {
					$v = $db->quote($qual['value']);
				}
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
		$sth = $db->prepare( $sql );
		if (!$sth) {
			throw new PDOException('cannot create statement handle');
		}

		//pretty logging
		$log_sql = $sql;
		foreach ($bind as $k => $v) {
			$log_sql = preg_replace("/$k/","'$v'",$log_sql,1);
		}
		Dase_Log::debug('[DBO find] '.$log_sql);

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

	function findCount()
	{
		$db = $this->_dbGet();
		$sets = array();
		$bind = array();
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
				//allows is to add 'is null' qualifier
				if ('null' == $qual['value']) {
					$v = $qual['value'];
				} else {
					$v = $db->quote($qual['value']);
				}
				$sets[] = "$f $op $v";
			}
		}
		$where = join( " AND ", $sets );
		if ($where) {
			$sql = "SELECT count(*) FROM ".$this->table. " WHERE ".$where;
		} else {
			$sql = "SELECT count(*) FROM ".$this->table;
		}
		$sth = $db->prepare( $sql );
		if (!$sth) {
			throw new PDOException('cannot create statement handle');
		}
		$log_sql = $sql;
		foreach ($bind as $k => $v) {
			$log_sql = preg_replace("/$k/","'$v'",$log_sql,1);
		}
		Dase_Log::debug('[DBO findCount] '.$log_sql);
		$sth->execute($bind);
		return $sth->fetchColumn();
	}

	public static function query($sql,$params=array(),$return_object=false)
	{
		$db = self::_dbGet();
		$sth = $db->prepare($sql);
		if (!$sth) {
			$errs = $db->errorInfo();
			if (isset($errs[2])) {
				throw new Dase_DBO_Exception('could not create handle: '.$errs[2]);
			}
		}
		if ($return_object) {
			$sth->setFetchMode(PDO::FETCH_OBJ);
		} else {
			$sth->setFetchMode(PDO::FETCH_ASSOC);
		}
		if (!$sth->execute($params)) {
			$errs = $sth->errorInfo();
			if (isset($errs[2])) {
				throw new Dase_DBO_Exception('could not execute query: '.$errs[2]);
			}
		} else {
			foreach ($params as $bp) {
				$sql = preg_replace('/\?/',"'$bp'",$sql,1);
			}
			Dase_Log::debug("----------------------------");
			Dase_Log::debug("[DBO query]".$sql);
			Dase_Log::debug("----------------------------");
		}
		return $sth;
	}

	function update()
	{
		$db = $this->_dbGet();
		foreach( $this->fields as $key => $val) {
			if ('timestamp' != $key || !is_null($val)) { //prevents null timestamp as update
				$fields[]= $key." = ?";
				$values[]= $val;
			}
		}
		$set = join( ",", $fields );
		$sql = "UPDATE {$this->{'table'}} SET $set WHERE id=?";
		$values[] = $this->id;
		$sth = $db->prepare( $sql );
		Dase_Log::debug($sql . ' /// ' . join(',',$values));
		if (!$sth->execute($values)) {
			$errs = $sth->errorInfo();
			if (isset($errs[2])) {
				//throw new Dase_DBO_Exception('could not update '. $errs[2]);
			}
		}
	}

	function delete()
	{
		$db = $this->_dbGet();
		$sth = $db->prepare(
			'DELETE FROM '.$this->table.' WHERE id=:id'
		);
		Dase_Log::debug("deleting id $this->id from $this->table table");
		return $sth->execute(array( ':id' => $this->id));
		//probably need to destroy $this here
	}

	//implement SPL IteratorAggregate:
	//now simply use 'foreach' to iterate 
	//over object properties
	public function getIterator()
	{
		return new ArrayObject($this->fields);
	}

	public function asArray()
	{
		foreach ($this as $k => $v) {
			$my_array[$k] = $v;
		}
		return $my_array;
	}

	public function asJson()
	{
		Dase_Json::get($this->asArray());
	}
}
