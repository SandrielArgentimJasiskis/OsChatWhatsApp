<?php

    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;

    class ModelPagesLogin extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Model {
        
        public function login($user, $pass) {
            $data = array();
            
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "users WHERE user = '" . $this->secure->escape_sql($user) . "' AND pass = '" . $this->secure->escape_sql($this->secure->hash($pass)) . "'");
            
            return ($query['num_rows'] == '1') ? $query['rows'][0]['id'] : false;
        }
    }
    