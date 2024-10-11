<?php

    namespace AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC;

    class Controller {
        
		use \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Traits\Call;
		use \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Traits\Constructor;
		use \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Traits\Load;
		
		public function get_configs($locale) {
		    return $this->form->get_fields($locale);
		}
		
    }
    