<?php

    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;

    class ModelWebhookUsers extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Model {
        public function getUserById($user_id) {
            $query = $this->db->query("SELECT *, user, fullname FROM " . DB_PREFIX . "settings INNER JOIN " . DB_PREFIX . "users ON " . DB_PREFIX . "users.id = " . DB_PREFIX . "settings.user_id WHERE " . DB_PREFIX . "users.id = '" . $this->secure->to_int($user_id) . "'");
            
            return $query['rows'][0];
        }
        
        public function getUserByPhone($phone) {
            $query = $this->db->query("SELECT *, user, fullname FROM " . DB_PREFIX . "settings INNER JOIN " . DB_PREFIX . "users ON " . DB_PREFIX . "users.id = " . DB_PREFIX . "settings.user_id WHERE " . DB_PREFIX . "settings.phone_id = '" . $this->secure->escape_sql($phone) . "'");
            
            return $query['rows'][0];
        }
    }