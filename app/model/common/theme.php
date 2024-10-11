<?php
	
	namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
	
	class ModelCommonTheme extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Model {
		
		public function getThemeByID($theme_id) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "themes WHERE id = '" . $this->secure->to_int($theme_id) . "'");
			
			return ($query['num_rows'] == '1') ? $query['rows'][0] : false;
		}
		
		public function getFonts() {
		    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "fonts GROUP BY name ASC;");
		    
		    return $query['rows'];
		}
	}
	