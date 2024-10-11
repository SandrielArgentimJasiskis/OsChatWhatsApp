<?php

    namespace AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Traits;

    trait load {
        
        public function load_controller($file, $function = 'index', $args = array()) {
                $method_get = 'get' . $file;
                
                if (file_exists(DIR_APP . 'controller/' . $file . '.php')) {
                    require_once(DIR_APP . 'controller/' . $file . '.php');
                }
                
                $new_file = 'controller_' . str_replace('/', '_', $file);
                $new_class = 'controller' . str_replace('/', '', $file);
                $new_class = str_replace('_', '', $new_class);
                
                $namespace = '\AppMVCSandrielArgentim\AppMVC\MVC\App\\';
                
                $full_new_class = $namespace . $new_class;
            
                $set_method_class = 'set' . $new_file;
                
                $this->$set_method_class(new $full_new_class($this, $file));
                $get_method_class = 'get' . $new_file;
                
                return call_user_func_array([$this->$get_method_class(), $function], $args);
        }
        
        public function load_language($file) {
			$this->load_model('pages/system');
			$language_id = $this->model_pages_system->get()['default_language_id'];
			$languages = $this->model_pages_system->getLanguages();
			
			foreach($languages as $language) {
				if ($language['id'] == $language_id) {
					$system_language = $language['path'];
				}
			}
			
            $xml = simplexml_load_file(DIR_APP . 'language/' . $system_language . '/' . $file . '.xml');
            
            return json_decode(json_encode($xml), true);
        }
        
        public function load_model($file, $function = 'index', $args = array()) {
			$method_get = 'get' . $file;
			
			if (file_exists(DIR_APP . 'model/' . $file . '.php')) {
				require_once(DIR_APP . 'model/' . $file . '.php');
			}
			
			$new_file = 'model_' . str_replace('/', '_', $file);
			$new_class = 'model' . str_replace('/', '', $file);
			$new_class = str_replace('_', '', $new_class);
			
			$namespace = '\AppMVCSandrielArgentim\AppMVC\MVC\App\\';
			
			$full_new_class = $namespace . $new_class;
		
			$set_method_class = 'set' . $new_file;
			
			$this->$set_method_class(new $full_new_class($this));
			$get_method_class = 'get' . $new_file;
			
			return call_user_func_array([$this->$get_method_class(), $function], $args);
        }
        
        public function load_view($file, $args = array()) {
			$this->load_model('pages/system');
			$theme_id = $this->model_pages_system->get()['default_theme_id'];
			$themes = $this->model_pages_system->getThemes();
			
			foreach($themes as $theme) {
				if ($theme['id'] == $theme_id) {
					$path = $theme['path'];
				}
			}
			
            return $this->template->render($file, $args, $path);
        }
        
        public function load_system($file, $args = false) {
            $new_property = explode("/", $file);
            $new_property = end($new_property);
            
            $new_class = str_replace("/", "\\", $file);
            
            $namespace = '\AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\\';
            
            $full_new_class = $namespace . $new_class;
            $set_method_class = 'set' . $new_property;
            
            if (!$args) {
                $args = $this;
            }
            
            $this->$set_method_class(new $full_new_class($args));
            $get_method_class = 'get' . $new_property;
            
            return $this->$get_method_class();
        }
		
    }
	