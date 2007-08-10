<?php
/**
 * Table Definition for search_cache
 */
require_once 'DB/DataObject.php';
require_once 'DataObjects/Attribute.php'; 
require_once 'DataObjects/Collection.php'; 

class DataObjects_Search_cache extends DB_DataObject 
{
	var $current_search; 
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'search_cache';                    // table name
    var $id;                              // int4(4)  not_null default_nextval%28public.search_cache_seq%29 primary_key
    var $query;                           // varchar(-1)  multiple_key
    var $timestamp;                       // timestamp(8)  default_%28now%29%3A%3Atimestamp%286%29%20with%20time%20zone multiple_key
    var $dase_user_id;                    // int4(4)  
    var $attribute_id;                    // int4(4)  
    var $collection_id_string;            // varchar(-1)  
    var $refine;                          // varchar(-1)  
    var $item_id_string;                  // varchar(-1)  
    var $exact_search;                    // int4(4)  
    var $is_stale;                        // bool(1)  default_false
    var $sort_by;                         // int4(4)  
    var $cb_id;                           // int4(4)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Search_cache',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	function getCurrentSearch() {
		$query = htmlspecialchars(urlencode($this->query),ENT_QUOTES,'UTF-8');
		$query = stripslashes($query);
		$refine = htmlspecialchars(urlencode($this->refine),ENT_QUOTES,'UTF-8');
		$refine = stripslashes($refine);
		//we leave off the 'query=' at start of $current_search because it reads more clearly in template
		//to use query={$current_search}
		$current_search = "$query&attribute_id=$this->attribute_id&collection_id=$this->collection_id_string&refine=$refine&exact_search=$this->exact_search&sort_by=$this->sort_by&cb_id=$this->cb_id";
		$this->current_search = $current_search;
		return $current_search;
	}

	function getItemIdArray() {
		$item_id_array = unserialize($this->item_id_string);
		$this->total = count($item_id_array);
		return $item_id_array; 
	}

	function makeSql($search_query,$cb_id = 0) {
		if ('browse_all_items' == $search_query->query) {
			//this will be over ridden below if control gets there 
			$search_query->query = '%';
			$browse_all = 1;
		}
		if ($cb_id) {
			$this->cb_id = $cb_id;  //'cause we use it in search echo for att pull down for sorting
			//while in CB you should ONLY see this collections items
			$where = " AND value.item_id IN (SELECT id FROM item WHERE collection_id = $cb_id)";
			$search_table_where = " AND search_table.item_id IN (SELECT id FROM item WHERE collection_id = $cb_id)";
		} else {
			$where = " AND value.item_id IN (SELECT id FROM item WHERE status_id = 0)";
			$search_table_where = " AND search_table.item_id IN (SELECT id FROM item WHERE status_id = 0)";
		}
		$query_string = addslashes($search_query->query);
		if ($search_query->exact_search) {
			if ($search_query->collection_id) {
				//need collection_id = 0 here for admin metadata
				$where .= " AND attribute_id IN (SELECT id FROM attribute WHERE collection_id = $search_query->collection_id)";
			}
			$sql = "
				SELECT item_id
				FROM value
				WHERE value_text = '$query_string' 
				$where
				";
			if ($search_query->refine) {
				$refine_array = $this->tokenizeQuoted($search_query->refine);	
				$refine_query_string = $this->getQueryString($refine_array,"refine");
				//check for query string here
				$sql = "
					SELECT value.item_id 
					FROM value, search_table
					WHERE value.value_text = '$query_string' 
					AND value.item_id = search_table.item_id
					$where
					$refine_query_string
					";
			}
		} elseif ($search_query->attribute_id) {
			//here's tricky bit to cover the case of admin_attributes (coll_id = 0):
			if ($search_query->admin_attribute_search) {
				$extra_clause = "AND value.item_id IN (select id FROM item WHERE collection_id = $search_query->collection_id)";
			}
			//we don't use search table since it's an attribute only search and they're munged together in search table
			$sql = "
				SELECT item_id
				FROM value
				WHERE attribute_id = $search_query->attribute_id
				AND value_text = '$query_string' 
				$extra_clause
				$where
				";
			if ($search_query->refine) {
				//but why use search_table here?  because this is NOT an attribute search -- it's the REFINEMENT of an attribute search
				$refine_array = $this->tokenizeQuoted($search_query->refine);	
				$refine_query_string = $this->getQueryString($refine_array,"refine");
				//check for query string here
				//here is a bug for you: if it is an admin_att and being refined, we lose the
				//$extra_clause from above (casue it chokes postgres!!)
				$sql = "
					SELECT value.item_id 
					FROM value, search_table
					WHERE attribute_id = $search_query->attribute_id
					AND value.value_text = '$query_string' 
					AND value.item_id = search_table.item_id
					$refine_query_string
					$extra_clause
					$where
					";
			}
		} else {
			//here's the most common case - straight up search OR refinement of a regular search
			//THIS IS A BIT MEssy and could use a bit 'o clean-up
			//allow for browse all items:
			if ($browse_all) {
				if ($search_query->refine) {
					$search_query->query = '%'; 
					$query_array = $this->tokenizeQuoted($search_query->query);	
					$refine_array = $this->tokenizeQuoted($search_query->refine);	
					$query_string = $this->getQueryString($query_array);
					$query_string .= $this->getQueryString($refine_array,"refine");
				} else {
					$query_string = ''; //i.e. there will be no query string in search
				}
			} else {
				$query_array = $this->tokenizeQuoted($search_query->query);	
				$refine_array = $this->tokenizeQuoted($search_query->refine);	
				$query_string = $this->getQueryString($query_array);
				$query_string .= $this->getQueryString($refine_array,"refine");
			}
			$sql = "
				SELECT item_id 
				FROM search_table 
				WHERE collection_id IN ($search_query->collection_id)	
				$query_string
				$search_table_where
				";
		}
		//change search_table to admin_search_table if we are using cb!!
		if ($cb_id) {
			$sql = str_replace('search_table','admin_search_table',$sql);
		}
		$this->sql = $sql;
		return $sql;
	}

	function getQueryString($query_array,$type="query",$prefix = ' AND ') {
		//this function gets us our string but also CAN (and usually will) get us our search echo
		$common_words_array = array('the','a','an','and','of','in','or','to');
		foreach ($query_array as $search_term) {
			if (in_array($search_term,$common_words_array)) {
				$this->short_term_array[] = "'$search_term'";
			} 
			if (strstr($search_term,' ')) {
				//cause if there IS a space, it means it is a quoted phrase
				//and I want to echo back the quotes
				$echo_search_term = "\"$search_term\"";
			} else {
				$echo_search_term = $search_term;
			}
			if ("refine" == $type) {
				$this->refine_echo_array[] = $echo_search_term;
			} else {
				$this->query_echo_array[] = $echo_search_term;
			}
			if (!in_array($search_term,$common_words_array)) {
				//three lines which attempt to implement 'NOT' searches
				//google-style
				if ('-' == substr($search_term,0,1)) {
					$search_term = substr($search_term,1);
					$search_term = addslashes($search_term);
					$like_array[] = "search_table.value_text NOT ILIKE '%$search_term%'";
				} else {
					$search_term = addslashes($search_term);
					$like_array[] = "search_table.value_text ILIKE '%$search_term%'";
				}
			}
		}
		if (is_array($like_array)) {
			$query_string = $prefix . join(' AND ',$like_array);
		}
		return $query_string;
	}

	//from php.net:
	function tokenizeQuoted($string) {
		for($tokens=array(), $nextToken=strtok($string, ' '); $nextToken!==false; $nextToken=strtok(' ')) {
			if($nextToken{0}=='"')
				$nextToken = $nextToken{strlen($nextToken)-1}=='"' ?
					substr($nextToken, 1, -1) : substr($nextToken, 1) . ' ' . strtok('"');
			$tokens[] = $nextToken;
		}
		return $tokens;
	}

	function performSearch($dsn,$search_query) {
		$dbh = DB::connect($dsn);
		$msg="an error occurred (no items found)";
		DB::isError($dbh) and header("Location:index.php?msg=$msg&collection_id=$search_query->collection_id");//die($dbh->getMessage());
		$item_id_array = array_unique($dbh->getCol($this->sql));
		//catch this error!!!!!!!!
		$msg="an error occurred (no items found).";
		DB::isError($item_id_array) and header("Location:index.php?msg=$msg&collection_id=$search_query->collection_id");//die($item_id_array->getMessage());

		if ($search_query->sort_by) {
			$item = new DataObjects_Item();
			//might as well stick this function in the Item class....
			$item_id_array = $item->sortIdArray($search_query->sort_by,$item_id_array);
		}

		$this->count = count($item_id_array);
		$this->item_id_string = serialize($item_id_array);
		$this->query = $search_query->query;
		$this->attribute_id = $search_query->attribute_id;
		$this->sort_by = $search_query->sort_by;
		$this->cb_id = $search_query->cb_id;
		$this->exact_search = $search_query->exact_search;
		$this->collection_id_string = $search_query->collection_id;
		$this->refine = $search_query->refine;
		$this->dase_user_id = $search_query->user->id;  //so we can display recent searches
		$id = $this->insert();
		$this->get($id);
	}

	function getResultSet($start,$max_items,$display,$anchor,$cart_id) {
		require_once 'SearchResultSet.php';
		$resultSet = new SearchResultSet;
		//for the "search within" (i.e. refine) form:
		$resultSet->query = htmlspecialchars($this->query,ENT_QUOTES,'UTF-8');
		$resultSet->refine = htmlspecialchars($this->refine,ENT_QUOTES,'UTF-8');
		$resultSet->collection_id = $this->collection_id_string;
		$resultSet->attribute_id = $this->attribute_id;
		$resultSet->sort_by = $this->sort_by;
		$resultSet->cb_id = $this->cb_id;
		$resultSet->exact_search = $this->exact_search;
		$resultSet->display = $display;
		$item_id_array = $this->getItemIdArray();
		$resultSet->total = count($item_id_array);
		$resultSet->start = $start;
		$resultSet->anchor = $anchor;
		$resultSet->max_items = $max_items;
		$item_num = $start;
		//like google, start=10 when first item is the 11th item in list
		if (is_array($item_id_array)) {
			$new_item_id_array = array_slice($item_id_array,$start,$max_items);
		} else {
			//throw error here!!
		}
		if (is_array($new_item_id_array)) {
			foreach ($new_item_id_array as $item_id) {
				$item_num++;
				$item =& new DataObjects_Item;
				$item->get($item_id);
				$item->item_num = $item_num;
				$item->getCollection();
				$item->getItemType();
				if ($this->sort_by) {
					$item->getSortDisplay($this->sort_by);
				}
				if ('titles' != $display) {
					$item->getThumbnail();
				}
				$item->inCart($cart_id);
				if ('titles' == $display) {
					$item->getSimpleTitle();
				}
				if ('contact' == $display) {
					$item->getSimpleTitle();
				}
				if ('list' == $display) {
					$item->getValues('list');
				}
				$resultSet->item_array[] = clone($item);
			}
		}
		$result_set_size = count($resultSet->item_array);
		$resultSet->last_item_num = $item_num;

		if (($resultSet->total - $resultSet->last_item_num) < $max_items) {
			$resultSet->next_set_size = $resultSet->total - $resultSet->last_item_num;
		} else {
			$resultSet->next_set_size = $max_items;
		}
		if (($start - $max_items) >= 0) {
			$resultSet->prev_set_size = $max_items;
		} else {
			$resultSet->prev_set_size = 0;
		}
		if ($resultSet->total > $result_set_size) {
			$resultSet->display_pager = 1;
		}
		return $resultSet;
	}

	function getSearchEcho() {
		if (!$this->query_echo_array) {
			//run appropriate functions to get our search echo
			//i'm not grabbing return value, because I'm just 
			//constructing search echo arrays (a but messy, I'm afraid)
			$query_array = $this->tokenizeQuoted($this->query);	
			$this->getQueryString($query_array);
		}

		if (!$this->refine_echo_array) {
			//see comment above
			$refine_array = $this->tokenizeQuoted($this->refine);	
			$this->getQueryString($refine_array,"refine");
		}
		if ($this->attribute_id) {
			$attribute =& new DataObjects_Attribute();
			$attribute->get($this->attribute_id);
			$collection =& new DataObjects_Collection;
			if ($attribute->collection_id) {
				$collection->get($attribute->collection_id);
			} else {
				//meaning this is an admin att w/ coll_id = 0
				//potential bug if collection_id_string (from 
				//seasrch_query->collection_id
				//is a string of ints and not a single int
				$collection->get($this->collection_id_string);
			}
			$collection->getAttributes();
			$attribute_name = $attribute->attribute_name;
			$collection_name = $collection->collection_name;
			$collection_ascii_id = $collection->ascii_id;
			$this->collection_id_string = $collection->id;
		} else {
			if (($this->collection_id_string) && (!strstr($this->collection_id_string,','))) {
				$collection =& new DataObjects_Collection;
				$collection->get($this->collection_id_string);
				$collection->getAttributesAlpha($this->cb_id);
				$collection_name = $collection->collection_name;
				$collection_ascii_id = $collection->ascii_id;
			}
		}
		if (is_array($this->short_term_array)) {
			$short_term_array = array_unique($this->short_term_array);
			$short_terms = join(', ',$short_term_array);
			$search_error = "The term(s) $short_terms were ignored.";
		}
		if (is_array($this->query_echo_array)) {
			if ((!$this->attribute_id) && (!$this->exact_search)) {
				//if it's NOT attribute specific and NOT exact, I want ANDs in there
				foreach ($this->query_echo_array as $query_echo) {
					$query_echo_array[] = htmlspecialchars($query_echo,ENT_QUOTES,'UTF-8');
				}
				$echo_query = join(' <code>AND</code> ',$query_echo_array);
			} else {
				//otherwise, I just want query string
				$echo_query = htmlspecialchars($this->query,ENT_QUOTES,'UTF-8');
			}
		}
		if (is_array($this->refine_echo_array)) {
			//IF it's a query, give me ANDs
			foreach ($this->refine_echo_array as $refine_echo) {
				$refine_echo_array[] = htmlspecialchars($refine_echo,ENT_QUOTES,'UTF-8');
			}
			$refine_echo_query = join(' <code>AND</code> ',$refine_echo_array);
		} else {
			$refine_echo_query = htmlspecialchars($this->refine,ENT_QUOTES,'UTF-8');
		}
		if (!$this->total) {
			$this->getItemIdArray(); //automatically sets total
		}
		//bit of a kluge -- a search_error is created when we get search echo even
		//if we don't want it (like when there is an attribute_id and we want the 
		//FULL query, NOT the query with common words stripped
		//added case where it's exact_search as well -pk 2/15/06
		//this is not exactly right since there is no search error if the 
		//refined text generates one
		if (($this->attribute_id) || ($this->exact_search)) {
			unset($search_error);
		}


		if ($this->sort_by) {
			$att = new DataObjects_Attribute;
			$att->get($this->sort_by);
			$this->sort_by_att_name = $att->attribute_name;
		}

		$search_echo = array(
				'query' => $echo_query,
				'refine' => $refine_echo_query,
				'search_error' => $search_error,
				'attribute_name' => $attribute_name,
				'attribute_id' => $this->attribute_id,
				'sort_by_att_name' => $this->sort_by_att_name,
				'exact_search' => $this->exact_search,
				'total' => $this->total,
				'collection' => $collection,
				'collection_name' => $collection_name,
				'collection_ascii_id' => $collection_ascii_id,
				'collection_id' => $this->collection_id_string,
				);
		unset($this->total);
		unset($this->query_echo_array);
		unset($this->refine_echo_array);
		$this->search_echo = $search_echo;
		return $search_echo;
	}
}
?>
