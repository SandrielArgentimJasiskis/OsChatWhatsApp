<?php
	
	namespace AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Traits;
	
	trait constructor {
		public function __construct($object, $config_file = false) {
			foreach($object as $key => $object) {
			    if ($key != 'request') {
                    $set = 'set' . $key;
                
                    $this->$set($object);
			    }
            }
            
            $this->load_system('requests/request');
            
            if ($config_file) {
                $this->data = array_merge($this->load_language($config_file), $this->load_language('common/error'));
                
                $this->data['config'] = $this->config->get_all($config_file);
                
                $this->data['fields'] = $this->data['config']['fields'] ?? false;
                
                if (substr($config_file, 0, 6) == 'pages/') {
                    $this->data['header'] = $this->load_controller('common/header');
                    $this->data['footer'] = $this->load_controller('common/footer');
                }
            }
        }
	}