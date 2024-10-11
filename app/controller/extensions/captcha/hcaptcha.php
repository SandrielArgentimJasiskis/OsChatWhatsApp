<?php

	namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
	
	class ControllerExtensionsCaptchaHCaptcha extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
	    
		public function index($data) {
			return $this->load_view('extensions/captcha/hcaptcha_form', $this->secure->remove_tags($data, '<i>'));
		}
		
		public function captcha() {
		    $this->load_model('pages/extensions');
		    $this->data['configs'] = $this->model_pages_extensions->getConfigExtensionByCode('hcaptcha');
		    
		    foreach($this->data['configs'] as $key => $config) {
		        if ($config['config'] == 'site_key') {
		            $this->data['site_key'] = $config['value'];
		        }
		    }
		    
		    return $this->load_view('extensions/captcha/hcaptcha_captcha', $this->secure->remove_tags($this->data));
		}
		
		public function validate() {
		    if (empty($this->request->post['h-captcha-response'])) {
		        return false;
		    }
		    
		    $data['response'] = $this->request->post['h-captcha-response'];
		    
		    $this->load_model('pages/extensions');
		    $this->data['configs'] = $this->model_pages_extensions->getConfigExtensionByCode('hcaptcha');
		    
		    foreach($this->data['configs'] as $key => $config) {
		        if ($config['config'] == 'secret_key') {
		            $data['secret'] = $config['value'];
		        }
		    }
		    
		    $ch = curl_init();
        
            curl_setopt($ch, CURLOPT_URL, 'https://hcaptcha.com/siteverify?secret=' . $data['secret'] . '&response=' . $data['response']);
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    
            $result = curl_exec($ch);
            
            $result = json_decode($result, true);
            
            return $result['success'] ?? false;
		}
	}
