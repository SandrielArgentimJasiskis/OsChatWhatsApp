<?php
    
    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
    
    class ModelWebhookAttendants extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Model {
        
        public function getAttendant($attendant_id) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "attendants WHERE id = '" . $this->secure->to_int($attendant_id) . "' LIMIT 1");
            
            return $query['rows'][0] ?? ['name' => '', 'number' => ''];
        }
        
        public function is_attendant_number($user_id, $from) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "attendants WHERE number = '" . $this->secure->escape_sql($from) . "' AND user_id = '" . $this->secure->to_int($user_id) . "' LIMIT 1");
            
            return ($query['num_rows'] == '1') ? $query['rows'][0] : false;
        }
    }
    