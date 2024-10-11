<?php
	
	namespace AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Traits;
		
	trait call {
		
		public function __call($method, $params = null) {
			if (substr($method, 0, 3) == 'set') {
				$key = substr($method, 3);
				
				$key = preg_replace('/\B([A-Z])/', '_$1', $key);
				
				$key = strtolower($key);
				
				$this->$key = $params[0];
			} elseif (substr($method, 0, 3) == 'get') {
				$key = substr($method, 3);
				
				$key = preg_replace('/\B([A-Z])/', '_$1', $key);

				
				$key = strtolower($key);
				
				return (!empty($this->$key)) ? $this->$key : false;
			}
		}
	}
	