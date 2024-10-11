<?php
    
    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
    
    class ModelWebhookSettings extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Model {
        public function getToken($user_id) {
            $query = $this->db->query("SELECT token FROM " . DB_PREFIX . "settings WHERE user_id = '" . $this->secure->to_int($user_id) . "'");
            
            return $query['rows'][0]['token'];
        }
    }