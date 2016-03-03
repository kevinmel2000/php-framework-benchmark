<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * LeafX2 database object (table, relation, etc)
 *

 */
class Leafx2_Dbobject {
    
    // table alias on select
    protected $alias;
    // auto-increment on primary key
    protected $autoinc = true;
    // database instance for this class
    protected $db;
    // database instance to use
    public $db_instance;
    // dynamic column name
    protected $dyncolumn = null;
    // dynamic columns data
    protected $dynfields = array();
    // table fields
    protected $fields = array();
    // primary key for the table
    protected $pkey;
    // table name in database
	protected $tablename;
	// where clauses
	protected $wheres = array();
	// group by clauses
	protected $groups = array();
	// order clauses
	protected $orders = array();
	// limit clauses
	protected $limit;
	// offset clauses
	protected $offset;
	// this class belongs to what class
	protected $belongs_to = array();
	// this class has many to
	protected $has_manys = array();
	// joined entity on select
	protected $joins = array();
	// return type for query, default is object (object / array)
	public $rtype = "object";
	// field update marker
	protected $mark_update = array();
	// select marker, for specific select
	protected $select_fields = array();

    /**
     * Constructing class
     * @return void
     */
    public function __construct() {
        $this->tablename = get_class($this);
        $this->pkey = $this->tablename ."id";
        $this->db = Leafx2_Db::instance($this->db_instance);
    }

    /**
     * Create instance of this class
     * @return Leafx2_Dbobject
     */
    public static function instance() {
        $classname = get_called_class();
        return new $classname();
    }

    /**
     * @param  $instance_name
     * @return Leafx2_Dbobject
     */
    public function reinstance($instance_name) {
        $this->db = Leafx2_Db::instance($instance_name);
        return $this;
    }
    
    /**
     * Define getter for table fields
     * @return mixed table field value
     */
    public function __get($key) {
        if (array_key_exists($key,$this->fields)) {
            return $this->fields[$key];
        }
        else if ($this->dyncolumn != null && array_key_exists($key,$this->dynfields)) {
            return $this->dynfields[$key];
        }

        return null;
    }
    
    /**
     * Define setter for table fields
     * @return void
     */
    public function __set($key,$value) {
        if (array_key_exists($key,$this->fields)) {
            // check marker, is it exists
            if (!is_array($this->mark_update))
                $this->mark_update = array_fill_keys($this->fields,0);
            $this->fields[$key] = $value;
            $this->mark_update[$key] = 1;
        }
        else {
            // add/update dynamic columns
            $this->dynfields[$key] = $value;
        }
    }
    
    /**
     * count designated row 
     * @return int num of row
     */
    public function count() {
        // sql query
        $sql = "select count(*) as numofrow from $this->tablename" . $this->get_where();
        $res = $this->db->query($sql);
        if (!empty($res))
            return $res[0]->numofrow;
        else
            return 0;
    }

    /**
     * insert into database
     * @param usedefault bool set true if you want to use default value for empty fields set by DBO
     * @return int inserted row
     */
	public function insert($usedefault = false) {
	    // constructing sql
	    $sql = "insert into $this->tablename (";
	    // imploding field names
	    $insfield = $this->fields;
	    if ($this->pkey != "" && $this->autoinc)
	        unset($insfield[$this->pkey]);
	    // set field values
	    $fnames = array();
	    $values = array();
	    foreach ($insfield as $field => $value) {
            if (($value == null && $usedefault) || $value != null) {
                $fnames[] = $field;
                $values[] = $this->db->quote($value);
            }
	    }
        // dynamic columns
        if ($this->dyncolumn != null && count($this->dynfields) > 0) {
            $fnames[] = $this->dyncolumn;
            $dyncol = array();
            foreach ($this->dynfields as $field => $value) {
                $dyncol[] = "'$field'";
                $dyncol[] = $this->db->quote($value);
            }
            $values[] = "COLUMN_CREATE(".implode(",",$dyncol).")";
        }
        // forge sql
	    $sql .= implode(",",$fnames) .") values (". implode(",",$values) .")";

	    // execute
	    $res = $this->db->query($sql);
	    return $res;
	}

    /**
     * update the data
     * @return int affected row
     */
	public function update($use_where = false) {
	    // constructing sql
	    $sql = "update $this->tablename set ";
	    // imploding field names
	    $updfield = $this->fields;
	    unset($updfield[$this->pkey]);
	    // set field values
	    $fields = array();
	    foreach ($updfield as $field => $value) {
            //if ($value != null)
            if ($this->mark_update[$field])
                $fields[] = $field." = ".$this->db->quote($value);
	    }
        // dynamic columns
        if ($this->dyncolumn != null && count($this->dynfields) > 0) {
            $dyncol = array();
            foreach ($this->dynfields as $field => $value) {
                $dyncol[] = "'$field'";
                $dyncol[] = $this->db->quote($value);
            }
            $fields[] = "$this->dyncolumn = COLUMN_ADD($this->dyncolumn,".implode(",",$dyncol).")";
        }
        // forge sql
	    $sql .= implode(",",$fields) . ($use_where? $this->get_where() : " where $this->pkey = '". $this->fields[$this->pkey] ."'");

	    // execute
	    $res = $this->db->query($sql);
	    return $res;
	}

	/**
     * update the data - non primary key based
     * @return int affected row
     */
	public function update_multi() {
	    return $this->update(true);
	}
	
	/**
	 * delete the data
	 * @return int num of deleted data
	 */
	public function delete($use_where = false) {
	    if ($this->fields[$this->pkey] == "") {
	        // no primary key set
	        // we just use the wheres that set, but, if wheres not set, prevent it from deleting entire table
	        if (empty($this->wheres)) return -1;
	    }
	    else {
	        // delete just those key
	        if (!$use_where) $this->where($this->pkey ." = '".$this->fields[$this->pkey]."'");
	    }
	    
	    $res = $this->db->query("delete from $this->tablename ".$this->get_where());
	    return $res;
	}

	/**
	 * delete multiple data
	 * @return int num of deleted data
	 */
	public function delete_multi() {
	    return $this->delete(true);
	}
	
	
	/**
	 * Gets all data from database
	 * @return array of data
	 */
	public function get($rtype = "object") {
		// returning data
    	return $this->db->query($this->get_sql(),$rtype);
	}

    /**
     * retrieve data by id, or by where clause - limited 1
     * @return int retrived row data
     */
    public function retrieve($use_where = false) {
		// set clause
		if (!$use_where)
		    $this->where("$this->pkey = '".$this->fields[$this->pkey]."'");
		// else, just use the where, make sure only retrieve one row
		else
		    $this->limit(1);
		// query data
		$result = $this->db->query($this->get_sql(),"array");
		// check the result
		if (count($result) > 0) {
		    foreach ($result[0] as $key => $value) {
		        if (array_key_exists($key,$this->fields))
                    $this->fields[$key] = $value;
                else if ($this->dyncolumn != null)
                    $this->dynfields[$key] = $value;
		    }
		    return count($result);
		}
		else {
		    return 0;
		}
    }

	/**
	 * Get the sql for querying database, usually to supply dbgridview
	 * @return  string SQL Query
	 */
	public function get_sql() {
	    $selects = count($this->select_fields) > 0? implode(",",array_keys($this->select_fields)) : implode(",",array_keys($this->fields));
	    // returning sql
		return "SELECT ".$selects." FROM ".$this->tablename. $this->get_where().$this->get_group().$this->get_order().$this->get_limit();
	}

    /** 
     * get the where clause
     * @return string SQL where clause
     */
    protected function get_where() {
        // constructing where clause
	    $first = true;
	    $where = "";
	    if (count($this->wheres) > 0) {
	        $where = " WHERE ";
	        foreach ($this->wheres as $w) {
	            $where .= ($first? "" : " AND ").$w;
	            $first = false;
	        }
	    }
	    return $where;
    }


    /**
     * get the group clause
     * @return string SQL group clause
     */
    protected function get_group() {
        if (!empty($this->groups)) {
            return " GROUP BY ".implode(",",$this->groups);
        }
        return "";
    }

    /**
     * get the order clause
     * @return string SQL order clause
     */
    protected function get_order() {
        if (!empty($this->orders)) {
            return " ORDER BY ".implode(",",$this->orders);
        }
        return "";
    }
    
    /**
     * get the limit clause
     * @return string SQL limit clause
     */
    protected function get_limit() {
        if ($this->limit != "") {
            return " LIMIT $this->limit OFFSET $this->offset";
        }
        return "";
    }

    /**
     * set specific select fields
     * @return Leafx2_Dbobject
     */
    public function select() {
        $params = func_get_args();
        if (is_array($params[0]))
            $params = $params[0];
        if (count($params) > 0) {
            $fields = array();
            foreach ($params as $col) {
                if (array_key_exists($col,$this->fields)) {
                    $fields[] = $col;
                }
                else if ($this->dyncolumn != "") {
                    // dynamic column enabled
                    $fields[] = "COLUMN_GET($this->dyncolumn,'$col' as char) as $col";
                }
            }
            if (count($fields) > 0)
                $this->select_fields = array_flip($fields);
        }
        return $this;
    }

    /**
	 * Add where clause
	 * @param string a where clause
	 * @return Leafx2_Dbobject
	 */
	public function where($where) {
	    $this->wheres[] = $where;
	    return $this;
	}

	/**
	 * Add group clause
	 * @param string a group clause
	 * @return Leafx2_Dbobject
	 */
	public function group_by($group) {
	    $this->groups[] = $group;
	    return $this;
	}

	/**
	 * Add order clause
	 * @param string an order clause
	 * @return Leafx2_Dbobject
	 */
	public function order_by($order,$sort = "ASC") {
	    $this->orders[] = $order." ".$sort;
	    return $this;
	}
	
    /**
     * set the limit
     * @param limit int num of data to be obtained
     * @param offset int offset from first row
     * @return Leafx2_Dbobject
     */
    public function limit($limit,$offset = 0) {
        $this->limit = $limit;
        $this->offset = $offset;
        return $this;
    }

    /**
     * set this class reference belong to another class
     * @param string class name
     * @param string foreign key to designated class
     * @return void
     */
    protected function belong_to($classname,$fkeyname,$options = array()) {
        if (!isset($this->belongs_to[$classname])) {
            $this->belongs_to[$classname] = array("fkey" => $fkeyname,"options" => $options);
        }
    }

    /**
     * join to another entity
     * @param string class name
     * @return void
     */
    public function join($classname) {
        if (!in_array($classname,$this->joins))
            $this->joins[] = $classname;
    }
    
	/**
	 * Reset all clause
	 * @return Leafx2_Dbobject
	 */
	public function reset() {
	    $this->wheres = array();
	    $this->groups = array();
	    $this->orders = array();
	    $this->select_fields = array();
	    $this->mark_update = array();
	    $this->limit = "";
	    $this->offset = "";
	    return $this;
	}

	/**
     * @param  $input any inputed data
     * @return string quoted sql string
     */
	public function quote($input) {
	    return $this->db->quote($input);
	}

    /**
     * @param $vars input parameter
     * @return int Inserted id
     * @throws Database_Exception on invalid input
     * @throws Database_Exception on no matching field key
     * @throws Exception
     */
    public function ez_insert($vars) {
        if (!is_array($vars))
            throw new Database_Exception("Invalid input",null,445);
        else {
            $match = false;
            foreach ($vars as $key => $val) {
                if (isset($this->fields[$key]) || $this->dyncolumn != "") {
                    $this->{$key} = $val;
                    $match = true;
                }
            }
            if (!$match) throw new Database_Exception("No matching field key",null,454);
            else {
                try {
                    $res = $this->insert();
                }
                catch (Database_Exception $de) {
                    throw $de;
                }
                return $res;
            }
        }
    }

    /**
     * Easy update
     * @param $vars input parameters
     * @param bool $use_where
     * @return int
     * @throws Database_Exception
     * @throws Database_Exception
     * @throws Exception
     */
    public function ez_update($vars,$use_where = false) {
        if (!is_array($vars))
            throw new Database_Exception("Invalid input",null,476);
        else {
            $match = false;
            foreach ($vars as $key => $val) {
                if (isset($this->fields[$key]) || $this->dyncolumn != "") {
                    $this->{$key} = $val;
                    $match = true;
                }
            }
            if (!$match) throw new Database_Exception("No matching field key",null,485);
            else {
                try {
                    $res = $this->update($use_where);
                }
                catch (Database_Exception $de) {
                    throw $de;
                }
                return $res;
            }
        }
    }
}