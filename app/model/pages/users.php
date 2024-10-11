<?php
    
    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
    
    class ModelPagesUsers extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Model {
        
        public function add($data) {
            $data = json_decode($data, true);
            
            $access = (!empty($data['access'])) ? json_encode($data['access']) : json_encode(array());
            
            $this->db->query("INSERT INTO " . DB_PREFIX . "users (user, fullname, pass, access, limitations) VALUES ('" . $this->secure->clear($data['user']) . "', '" . $this->secure->clear($data['fullname']) . "', '" . $this->secure->hash($data['pass']) . "', '" . $this->secure->clear($access) . "', '" . $this->secure->clear(json_encode($data['limitations'])) . "');");
            
            $data['id'] = $this->db->getLastId();
            
            $this->load_model('pages/settings', 'add', (array)json_encode($data));
            
            return $data['id'];
        }
        
        public function addActivity($data) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "users_activities (user_id, activity, content, date) VALUES ('" . $this->secure->to_int($data['user_id']) . "', '" . $this->secure->clear($data['activity']) . "', '" . $this->secure->clear(json_encode($data['content'])) . "', NOW());");
        }
        
        public function edit($data) {
            $this->log->write($data);
            
            $data = json_decode($data, true);
            
            $access = (!empty($data['access'])) ? json_encode($data['access']) : json_encode(array());
			
			$pass_query = ($data['pass'] != '') ? "pass = '" . $this->secure->hash($data['pass']) . "', " : '';
            
            $this->db->query("UPDATE " . DB_PREFIX . "users SET user = '" . $this->secure->clear($data['user']) . "', fullname = '" . $this->secure->clear($data['fullname']) . "', " . $pass_query . "access = '" . $this->secure->clear($access) . "', limitations = '" . $this->secure->clear(json_encode($data['limitations'])) . "' WHERE id = '" . $this->secure->to_int($data['id']) . "';");
            
            $this->load_model('pages/settings', 'edit', (array)json_encode($data));
        }
        
        public function delete($user_id) {
            
            $this->db->query("DELETE FROM " . DB_PREFIX . "users WHERE id = '" . $this->secure->to_int($user_id) . "'");
           
           $this->load_model('pages/settings', 'delete', (array)$user_id);
        }
        
        public function getAllUsers() {
            $users = $this->db->query("SELECT id, user, fullname FROM " . DB_PREFIX . "users");
            
            return $users['rows'];
        }
        
        public function getUser($id) {
            $user = $this->db->query("SELECT id, user, fullname, access, limitations FROM " . DB_PREFIX . "users WHERE id = '" . $this->secure->to_int($id) . "' LIMIT 1");
            
            
            return $user['rows'][0];
        }
        
        public function getUserByEmail($email) {
            $user = $this->db->query("SELECT fullname FROM " . DB_PREFIX . "users WHERE email = '" . $this->secure->clear($email) . "' LIMIT 1");
            
            
            return $user['rows'][0] ?? false;
        }
        
        public function getActivities($limit = 1) {
            $query = $this->db->query("SELECT fullname AS user, activity, content, date FROM " . DB_PREFIX . "users_activities INNER JOIN " . DB_PREFIX . "users ON " . DB_PREFIX . "users_activities.user_id = " . DB_PREFIX . "users.id ORDER BY " . DB_PREFIX . "users_activities.id DESC LIMIT " . $this->secure->to_int($limit));
            
            return ($query['num_rows'] > 1) ? $query['rows'] : false;
        }
        
        public function userExists($user_id, $user) {
			$query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "users where id != '" . $this->secure->to_int($user_id) . "' AND user = '" . $this->secure->escape_sql($user) . "'");
			
			return ($query['rows'][0]['total'] == 0) ? false : true;
		}
    }
    