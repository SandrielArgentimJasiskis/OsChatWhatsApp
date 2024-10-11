<?php

    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
    
    class ModelPagesAttendants extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Model {
        
        public function add($data) {
            $data = json_decode($data, true);
            
            $this->db->query("INSERT INTO " . DB_PREFIX . "attendants (user_id, name, number) VALUES ('" . $this->secure->to_int($this->user->getId()) . "', '" . $this->secure->clear($data['attendant_name']) . "', '" . $this->secure->clear($data['attendant_number']) . "');");
            
            return $this->db->getLastId();
        }
        
        public function edit($data) {
            $data = json_decode($data, true);
            
            $this->db->query("UPDATE " . DB_PREFIX . "attendants SET name = '" . $this->secure->clear($data['attendant_name']) . "', number = '" . $this->secure->clear($data['attendant_number']) . "' WHERE id = '" . $this->secure->to_int($data['id']) . "'");
        }
        
        public function delete($attendant_id) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "attendants WHERE id = '" . $this->secure->to_int($attendant_id) . "'");
        }
        
        public function getAllUserAttendants($user_id) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "attendants WHERE user_id = '" . $this->secure->to_int($user_id) . "'");
            
            return ($query['num_rows'] != '0') ? $query['rows'] : false;
        }
        
        public function getUserAttendants($user_id) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "attendants WHERE user_id = '" . $this->secure->to_int($user_id) . "'");
            
            return ($query['num_rows'] != '0') ? $query['rows'] : false;
        }
        
        public function getTotalUserAttendants($user_id) {
            $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "attendants WHERE user_id = '" . $this->secure->to_int($user_id) . "'");
            
            return $query['rows'][0]['total'];
        }
        
        public function getAttendant($attendant_id) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "attendants WHERE id = '" . $this->secure->to_int($attendant_id) . "' AND user_id = '" . $this->user->getId() . "' LIMIT 1");
            
            return $query['rows'][0] ?? false;
        }
        
        public function attendantExists($number, $user_id, $attendant_id = false) {
        $attendant_query = ($attendant_id) ? " AND id != '" . $this->secure->to_int($attendant_id) . "'" : "";
        
			$query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "attendants where id = '" . $this->secure->to_int($user_id) . "' AND number = '" . $this->secure->escape_sql($number) . "'" . $attendant_query);
			
			return ($query['rows'][0]['total'] == 0) ? false : true;
		}
    }
    