<?php
    
    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
    
    class ModelPagesExtensions extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Model {
        
        public function edit($extension_id, $configs, $is_to_all_users = 0) {
            $this->log->write('Conteúdo da variável $is_to_all_users: ' . $is_to_all_users);
            
            if ($is_to_all_users == 1) {
                $this->db->query("DELETE FROM " . DB_PREFIX . "extensions_config WHERE extension_id = '" . $this->secure->to_int($extension_id) . "'");
            } else {
                $this->db->query("DELETE FROM " . DB_PREFIX . "extensions_config WHERE extension_id = '" . $this->secure->to_int($extension_id) . "' AND user_id = '" . $this->secure->to_int($this->user->getId()) . "'");
            }
            
            foreach($configs as $key => $config) {
                $query_to_all_users = ($is_to_all_users == 0) ? "'" . $this->secure->to_int($this->user->getId()) . "'" : "0";
                
                $query = $this->db->query("INSERT INTO `" . DB_PREFIX . "extensions_config`(`extension_id`, `user_id`, `config`, `value`) VALUES ('" . $this->secure->to_int($extension_id) . "',  " . $query_to_all_users . ", '" . $this->secure->clear($key) . "', '" . $this->secure->clear($config) . "')");
            }
        }
        
        public function getConfigExtension($extension_id, $is_to_all_users) {
            $query_to_all_users = ($is_to_all_users == 0) ? "user_id = '" . $this->secure->to_int($this->user->getId()) . "'" : "user_id = '0'";
            
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extensions_config WHERE extension_id = '" . $this->secure->to_int($extension_id) . "' AND " . $query_to_all_users);
            
            return $query['rows'];
        }
        
        public function getConfigExtensionByCode($extension_code) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extensions_config INNER JOIN " . DB_PREFIX . "extensions ON " . DB_PREFIX . "extensions.id = " . DB_PREFIX . "extensions_config.extension_id WHERE " . DB_PREFIX . "extensions.code = '" . $this->secure->escape_sql($extension_code) . "'");
            
            return $query['rows'];
        }
        
        public function getConfigStatusExtension($extension_id, $is_to_all_users) {
            $query_to_all_users = ($is_to_all_users == 0) ? "'" . $this->secure->to_int($this->user->getId()) . "'" : "'0'";
            
            $query = $this->db->query("SELECT value FROM " . DB_PREFIX . "extensions_config WHERE extension_id = '" . $this->secure->to_int($extension_id) . "' AND config = 'status' AND user_id = " . $query_to_all_users);
            
            return $query['rows'][0]['value'] ?? array();
        }
        
        public function getExtensionById($extension_id) {
            $query = $this->db->query("SELECT type, code FROM " . DB_PREFIX . "extensions WHERE id = '" . $this->secure->to_int($extension_id) . "' LIMIT 1");
            
            return $query['rows'][0];
        }
        
        public function getExtensions($extensions_type) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extensions WHERE type = '" . $this->secure->escape_sql($extensions_type) . "'");
            
            return $query['rows'];
        }
        
		public function extensionTypeStatusExists($extension_type, $extension_id) {

			$query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "extensions_config INNER JOIN " . DB_PREFIX . "extensions ON (" . DB_PREFIX . "extensions_config.extension_id = " . DB_PREFIX . "extensions.id) where extension_id != '" . $this->secure->to_int($extension_id) . "' AND type = '" . $this->secure->escape_sql($extension_type) . "' AND config = 'status' AND value = '1'");
			
			$this->log->write('Quantidade de extensoes do tipo ' . $extension_type . ' com o status habilitado: ' . $query['rows'][0]['total']);
			
			return ($query['rows'][0]['total'] == 0) ? false : true;
		}
    }
    