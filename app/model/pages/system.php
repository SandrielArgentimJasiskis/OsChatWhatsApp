<?php
	
	namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
	
	class ModelPagesSystem extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Model {
		
		public function edit($data) {
			foreach($data as $key => $data) {
				$this->db->query("UPDATE " . DB_PREFIX . "system SET value='" . $this->secure->clear($data) . "' WHERE config = '" . $this->secure->clear($key) . "'");
			}
		}
		
		public function get() {
			$data = array();
			
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "system");
			
			foreach($query['rows'] as $config) {
				$data[$config['config']] = $config['value'];
			}
			
			return $data;
		}
		
		public function getLanguages() {
			$data = array();
			
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "languages");
			
			return $query['rows'];
		}
		
		public function getThemes() {
			$data = array();
			
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "themes");
			
			return $query['rows'];
		}
	}
	