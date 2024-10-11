<?php

    namespace AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Data;
    
    class Log {
        
        public function __construct($file) {
            ini_set("log_errors", TRUE);
            ini_set('error_log', DIR_SYSTEM . 'logs/' . $file);
        }
        
        public function write($data) {
            error_log($data);
        }
        
    }
    