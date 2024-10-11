<?php
    
    namespace AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Config;
    
    class Config {
        public function get_all($location) {
            return require(DIR_APP . 'config/' . $location . '.php');
        }
        
        public function get_fields($location) {
            return $this->get_all($location)['fields'];
        }
    }