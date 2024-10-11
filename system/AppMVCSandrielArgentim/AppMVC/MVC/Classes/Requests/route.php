<?php

    namespace AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Requests;

    class Route {
		
		use \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Traits\Call;
		use \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Traits\Constructor {
			\AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Traits\Constructor::__construct as private __tConstruct;
		}
		use \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Traits\Load;
			
		public function __construct($object){
			$this->__tConstruct($object);
			
			$this->load_system('data/secure');
		}
		
        public function get() {
			$route = explode('/', $this->request->get['route'])[0];
			
			return $this->load_model('common/route', 'getRoute', [$route]);
        }
		
		public function get_all() {
			return $this->load_model('common/route', 'getAllRoutes');
		}
    }
    