<?php
    
    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
    
    class ModelPagesSettings extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Model {
        
        public function add($data) {
            $data = json_decode($data, true);
            
            $this->db->query("INSERT INTO " . DB_PREFIX . "settings (user_id, app_id, whatsapp_business_account_id, phone_id, token) VALUES ('" . $this->secure->to_int($data['id']) . "', '" . $this->secure->clear($data['app_id']) . "', '" . $this->secure->clear($data['whatsapp_business_account_id']) . "', '" . $this->secure->clear($data['phone_id']) . "', '" . $this->secure->clear($data['token']) . "');");
        }
        
        public function edit($data) {
            $data = json_decode($data, true);
            
            $query = "UPDATE " . DB_PREFIX . "settings SET ";
            
            if (!empty($data['app_id'])) {
                $query .= "app_id = '" . $this->secure->clear($data['app_id']) . "',";
            }
            if (!empty($data['whatsapp_business_account_id'])) {
                $query .= "whatsapp_business_account_id = '" . $this->secure->clear($data['whatsapp_business_account_id']) . "',";
            }
            if (!empty($data['token'])) {
                $query .= "token = '" . $this->secure->clear($data['token']) . "',";
            }
            
            $query .= "phone_id = '" . $this->secure->clear($data['phone_id']) . "'";
            
            $query .=  " WHERE user_id = '" . $this->secure->to_int($data['id']) . "'";
            
            $this->db->query($query);
        }
        
        public function delete($user_id) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "settings WHERE user_id = '" . $this->secure->to_int($user_id) . "';");
        }
        
        
        public function getSettings($user_id) {
            $settings = $this->db->query("SELECT * FROM " . DB_PREFIX . "settings WHERE user_id = '" . $this->secure->to_int($user_id) . "' LIMIT 1");
            
            return (!empty($settings['rows'][0])) ? $settings['rows'][0] : false;
        }
		
		public function phoneExists($user_id, $phone) {
			$query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "settings where user_id != '" . $this->secure->to_int($user_id) . "' AND phone_id = '" . $this->secure->escape_sql($phone) . "'");
			
			return ($query['rows'][0]['total'] == 0) ? false : true;
		}
    }