<?php
/**
 * Class LeafFileSystem
 *
 * class for manipulating and retrieving file/directory
 *
 * @version  1.0
 * @author   Agung Prihanggoro <agung@.co.id>
 */
class Leafx2_Filesystem {
    
    /**
     * get file content
     * File can be any kind, but these functions merely a simple 'ascii-file reader'
     * so use it wisely
     * @param string $filename File to be read
     * @return string $contents File contents returned
     * @access public
     */
    public static function get ($filename) {
        //echo getcwd() . $filename;
        $handle = fopen ($filename, "r");
        $contents = fread ($handle, filesize ($filename));
        fclose ($handle);
        return $contents;
    }
    
    /**
     * add file content
     * Add string to end-of-file, if file already exist, it append.
     * Otherwise, attempt to create new file
     * @param string $filename File to be append
     * @return void
     * @access public
     */
    public static function add ($filename,$str) {
        $handle = fopen ($filename, "a+");
        fwrite ($handle,$str);
        fclose ($handle);
    }
    
    /**
     * rewrite file content
     * Rewrite file from beginning file content, if file found,
     * otherwise, attempt to create new one
     * @param string $filename File to be rewrited
     * @access public
     */
    public static function rewrite ($filename,$str) {
        $handle = fopen ($filename, "w+");
        $res = fwrite ($handle,$str);
        fclose ($handle);
        return $res;
    }

    /**
     * search files within given directory
     * @param string $dir directory
     * @param string $card criterion
     * @return array files
     */
    public static function globber ($dir,$card = "*") {
        $data = array();
        $handle = opendir($dir);
        while ($file = readdir($handle)) {
            if ($card == "*") {
                array_push($data,$file);
            }
            else if (substr_count(strtolower($file),strtolower($card)) > 0) {
                array_push($data,$file);
            }
        }
        closedir($handle);
        
        return $data;
    }
    
    public static function grep ($dir,$card = "*",$flag = 0) {
        $data = array();
        $handle = opendir($dir);
        while ($file = readdir($handle)) {
            $found = false;
            if ($flag == 1 && is_dir($dir.$file) && $file != "." && $file != "..") $found = true;
            if ($flag == 2 && is_file($dir.$file)) $found = true;
            if ($flag == 0) $found = true;
            
            if ($found) {
                if ($card == "*") {
                    array_push($data,$file);
                }
                else if (substr_count(strtolower($file),strtolower($card)) > 0) {
                    array_push($data,$file);
                }
            }
        }
        closedir($handle);
        
        return $data;
    }
    
}
?>