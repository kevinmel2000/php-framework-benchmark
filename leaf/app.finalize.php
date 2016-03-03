<?php defined('SYSPATH') OR die('No direct access allowed.');
    /**
     * Finalizing application
     *
     * @author
     * @copyright  (c) 2010
     */
    
    // memory usage
    $musage = round(memory_get_peak_usage(true) / (1024 * 1024),3);
    // stop timer
    list($usec, $sec) = explode(" ", microtime()); 
    $this->benchmark['controller_stop'] = ((float)$usec + (float)$sec); 
    $this->html_container->add('HTML_FOOTER_LEFT','Rendered in '.round($this->benchmark['controller_stop']-$this->benchmark['controller_start'],4) . " sec using $musage MB");
    $this->html_container->add('HTML_FOOTER_RIGHT','&copy; 2014 Akhdani');

