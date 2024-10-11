<?php
	
	namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
	
	class ModelCommonRoute extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Model {
		
		public function getRoute($route) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "routes WHERE route = '" . $this->secure->escape_sql($route) . "'");
			
			return ($query['num_rows'] == '1') ? $query['rows'][0]['path'] : false;
		}
		
		public function getAllRoutes() {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "routes");
			
			return $query['rows'];
		}
	}
	