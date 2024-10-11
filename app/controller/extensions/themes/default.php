<?php

	namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
	
	class ControllerExtensionsThemesDefault extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
	    
		public function index($data) {
		    $data['fonts'] = $this->load_model('common/theme', 'getFonts');
		    
			return $this->load_view('extensions/themes/default', $this->secure->remove_tags($data, '<i>'));
		}
	}
	