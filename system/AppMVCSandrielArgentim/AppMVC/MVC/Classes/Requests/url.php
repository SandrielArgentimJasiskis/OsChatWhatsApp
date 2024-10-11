<?php

    namespace AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Requests;

    class Url {
		
		use \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Traits\Call;
		use \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Traits\Constructor {
			\AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Traits\Constructor::__construct as private __tConstruct;
		}
		use \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Traits\Load;
		
		public function __construct($object){
			$this->__tConstruct($object);
			
			$this->setRoutes($this->route->get_all());
		}
		
        public function get() {
            $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
            
            return $protocol . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
        }
        
        public function link($url, $request = '') {
			$url = explode('/', $url);
			
			$action = end($url);
			
			array_pop($url);
			
			$url = implode('/', $url);
			
			foreach($this->getRoutes() as $route) {
				if ($route['path'] == $url) {
					$path = $route['route'] . '/';
				}
			}
			
            $url = $path . $action . '/' . $request;
			
            return URL_APP . $url;
        }
        
        public function redirect($url, $request = '') {
			$url = explode('/', $url);
			
			$action = end($url);
			
			array_pop($url);
			
			$url = implode('/', $url);
			
			foreach($this->getRoutes() as $route) {
				if ($route['path'] == $url) {
					$path = $route['route'] . '/';
				}
			}
			
            $url = $path . $action . '/' . $request;
			
            header("Location: " . URL_APP . $url);
            die();
        }
    }
    