<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Leaf Database class
 * used for encapsulating Kohana Database module for easier usage
 * with the same feature and security
 *

 * @copyright  (c) 2010
 */
define('DB_PARAM_SCALAR', 1);
define('DB_PARAM_OPAQUE', 2);
define('DB_PARAM_MISC',   3);

class Leafx2_Db {
    
    /**
     * @var object the kohana database instance
     */
    private $db;
    /**
     * @var array Historical sql
     */
    private $history = array();
    /**
     * @var instance of Leafx2 db
     */
    public static $instances = array();

    /**
     * @var bool Check if transaction already started
     */
    private $trans_started = false;
    
    /**
     * Returns a singleton instance of class
     *
     * @return  Leafx2_Db
     */
    public static function instance($name = NULL,$config = NULL) {
        $name = $name == NULL? 'default' : $name;
        //if (self::$instances[$name] === NULL) {
        if (!isset(self::$instances[$name])) {
            // Create a new instance
            self::$instances[$name] = new self($name,$config);
        }
        return self::$instances[$name];
    }
    
    /**
     * factory - alias of instance
     */
    public static function factory($name = NULL,$config = NULL) {
        return self::instance($name,$config);
    }
    
    /**
     * Constructing class
     * create a database instance
     * @return void
     */
    public function __construct($name = NULL,$config = NULL) {
        $this->db = Database::instance($name,$config);
    }
    
    /**
     * Disconnect from the database when the object is destroyed.
     *
     * @return  void
     */
    public function __destruct() {
        $this->db->disconnect();
    }
    
    /**
     * execute a simple query
     * @return mixed Return an array if a select sql, or affected row on other sql. Returning false on error
     */
    public function query($sql,$rtype = 'object') {
        // determine what kind of query
        $asql = preg_split('/ \s*/',$sql);
        switch (strtolower($asql[0])) {
            case "select" :
                $qtype = Database::SELECT; break;
            case "insert" :
                $qtype = Database::INSERT; break;
            case "update" :
                $qtype = Database::UPDATE; break;
            case "delete" :
                $qtype = Database::DELETE; break;
            default :
                $qtype = null;
        }
        if ($qtype == null) {
            return FALSE;
        }
        else if ($qtype == Database::SELECT) {
            switch ($rtype) {
                case "array" :
                    $result = $this->db->query($qtype,$sql,false)->as_array(); break;
                case "object" :
                default :
                    $result = $this->db->query($qtype,$sql,true)->as_array();
            }
            $this->record("select",$sql,count($result));
            return $result;
        }
        else if ($qtype == Database::INSERT) {
            $result = $this->db->query($qtype,$sql,true);
            $this->record($asql[0],$sql,$result[1]);
            return $result[0];
        }
        else {
            $result = $this->db->query($qtype,$sql,true);
            $this->record($asql[0],$sql,$result);
            return $result;
        }
    }
    
    /**
     * execute multiple query
     * @param array data array of integer-indexed-array data
     * @param string statement
     * @return mixed number of affected row or false if a query failed
     */
    public function queries($data,$statement) {
        // determine what kind of query
        $asql = preg_split('/ \s*/',$statement);
        switch (strtolower($asql[0])) {
            case "select" :
                $qtype = null; break;
            case "insert" :
                $qtype = Database::INSERT; break;
            case "update" :
                $qtype = Database::UPDATE; break;
            case "delete" :
                $qtype = Database::DELETE; break;
            default :
                $qtype = null;
        }
        
        // prepare query
        $tokens   = preg_split('/((?<!\\\)[&?!])/', $statement, -1, PREG_SPLIT_DELIM_CAPTURE);
        $token     = 0;
        $types     = array();
        $newtokens = array();

        foreach ($tokens as $val) {
            switch ($val) {
                case '?':
                    $types[$token++] = DB_PARAM_SCALAR;
                    break;
                case '&':
                    $types[$token++] = DB_PARAM_OPAQUE;
                    break;
                case '!':
                    $types[$token++] = DB_PARAM_MISC;
                    break;
                default:
                    $newtokens[] = preg_replace('/\\\([&?!])/', "\\1", $val);
            }
        }
        
        // traversing on data
        $affrow = 0;

        foreach ($data as $item) {
            $aquery = "";
            $idx = 0;
            // on each item, traverse element
            foreach ($item as $elm) {
                $aquery .= $newtokens[$idx];
                if ($types[$idx] == DB_PARAM_SCALAR) {
                    $aquery .= $this->db->quote($elm);
                }
                else if ($types[$idx] == DB_PARAM_OPAQUE) {
                    $fp = @fopen($value, 'rb');
                    if (!$fp) {
                        return FALSE;
                    }
                    $aquery .= $this->db->quote(fread($fp, filesize($elm)));
                    fclose($fp);
                }
                else {
                    $aquery .= $elm;
                }
                $idx++;
            }
            $aquery .= $newtokens[$idx];
            // execute the query
            if ($qtype == Database::INSERT) {
                $result = $this->db->query($qtype,$aquery,true);
                $this->record($asql[0],$aquery,$result[1]);
                if ($result[1] > 0) $affrow += $result[1];
                else $affrow += $result[1];
            }
            else {
                $result = $this->db->query($qtype,$aquery,true);
                $this->record($asql[0],$aquery,$result);
                if ($result > 0) $affrow+= $result;
                else $affrow += 0;
            }
        }
        return $affrow;
    }

    /**
     * @param  $value any value to be quoted
     * @return string
     */
    public function quote($value) {
        return $this->db->quote($value);
    }

    /**
     * Get sql-executed history
     * @return array
     */
    public function get_history() {
        return $this->history;
    }

    /**
     * record query for history purpose
     * @param  $qtype
     * @param  $sql
     * @param  $result
     * @return void
     */
    private function record($qtype,$sql,$result) {
        // array of type,sql statement,num of result
        $this->history[] = array($qtype,$sql,$result);
    }

    /**
     * Begin transaction
     * @return bool
     */
    public function begin() {
        if (!$this->trans_started) {
            $this->trans_started = true;
            return $this->db->begin();
        }
        else return false;
    }

    /**
     * Commit transaction
     * @return bool
     */
    public function commit() {
        $this->trans_started = false;
        return $this->db->commit();
    }

    /**
     * Rollback transaction
     * @return bool
     */
    public function rollback() {
        $this->trans_started = false;
        return $this->db->rollback();
    }

}