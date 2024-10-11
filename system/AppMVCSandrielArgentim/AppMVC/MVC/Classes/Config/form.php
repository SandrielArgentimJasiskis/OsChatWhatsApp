<?php
    
    namespace AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Config;
    
    class Form {
        public function get_fields($location) {
            return require_once(DIR_APP . 'config/' . $location . '.php');
        }
    }