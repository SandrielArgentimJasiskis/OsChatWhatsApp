<?php
    
    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
    
    class ControllerPagesSettings extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
        
        public function index() {
            $this->load_model('pages/system');
            
            if (empty($this->request->get['user_id'])) {
                $this->request->get['user_id'] = $this->user->getId();
            }
            
            if (!empty($this->request->post) && $this->validate()) {
                
                $this->request->post['id'] = $this->user->getId();
                
                $settings = $this->load_model('pages/settings', 'getSettings', (array)$this->request->post['id']);
                
                /* Adiciona ao histórico a modificação feita pelo usuário */
                $this->load_controller('common/history', 'addHistory', ['update_settings', json_encode($settings)]);
           
                $this->load_model('pages/settings', 'edit',
                (array)json_encode($this->request->post));
                
                $this->setMsg($this->data['text_msg_success']);
            }
            
            if ($this->getError()) {
                $this->data['error'] = $this->getError();
            }
            
            if ($this->getMsg()) {
                $this->data['msg'] = $this->getMsg();
            }
            
            $this->data['action'] = $this->url->link('pages/settings/index');
            
            $system = $this->model_pages_system->get();
            $this->data['api_domain'] = $system['api_domain'];
            $this->data['api_version'] = $system['api_version'];
            
            $settings = $this->load_model('pages/settings', 'getSettings', (array)$this->user->getId());
            
            $this->data['app_id'] = $this->request->post['app_id'] ?? $settings['app_id'];
            $this->data['whatsapp_business_account_id'] = $this->request->post['whatsapp_business_account_id'] ?? $settings['whatsapp_business_account_id'];
            $this->data['phone_id'] = $this->request->post['phone_id'] ?? $settings['phone_id'];
            $this->data['token'] = $this->request->post['token'] ?? $settings['token'];
            
            $this->data['phone_numbers'] = $this->getPhones($this->data);
            
			$this->load_model('pages/extensions');
		    
		    $extensions = $this->load_model('pages/extensions', 'getExtensions', ['captcha']);
            
            $this->data['extensions'] = array();
            foreach($extensions as $extension) {
                $is_to_all_users = $this->config->get_all('extensions/captcha/' . $extension['code'])['to_all_users'];
                
                if ($this->model_pages_extensions->getConfigStatusExtension($extension['id'], $is_to_all_users) == '1') {
                    $this->data['captcha'] = $this->load_controller('pages/login', 'load_captcha');
                }
            }
			
            /* Gera um token aleatório anti CSRF */
            $this->data['token_name'] = '_' . $this->secure->random(64);
            $this->data['token_value'] = $this->secure->random(64);
            $this->session->data('token', [
                'name'  => $this->data['token_name'], 
                'value' => $this->data['token_value']
            ]);
        
            $this->template->display($this->load_view('pages/settings', $this->secure->remove_tags($this->data, '', ['header', 'footer', 'captcha'])));
        }
        
        public function getPhones($data) {
            $this->load_model('pages/system');
            $system = $this->model_pages_system->get();
            
            $ch = curl_init();
        
            curl_setopt($ch, CURLOPT_URL, 'https://' . $this->secure->clear($system['api_domain']) . '/' . $this->secure->clear($system['api_version']) . '/' . $this->secure->clear($data['whatsapp_business_account_id']) . '/phone_numbers');
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            
            $headers = array();
            $headers[] = 'Authorization: Bearer ' . $this->secure->clear($data['token']);
            $headers[] = 'Content-Type: application/json';
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                return curl_errno($ch);
            }
            
            curl_close($ch);
            
            $result = json_decode($result, true);
            
            return $result['data'] ?? false;
        }
        
        public function validate() {
            if (!isset($this->request->post[$this->request->session['token']['name']])) {
                $this->secure->access_denied();
            }
            
            if ($this->request->post[$this->request->session['token']['name']] !== $this->request->session['token']['value']) {
                $this->secure->access_denied();
            }
            
            if (!empty($this->request->get['app_id'])) {
                if (strlen($this->request->post['app_id']) < 14) {
                    $this->setError($this->data['text_error_app_id_format']);
                    return false;
                }
                
                if (strlen($this->request->post['whatsapp_business_account_id']) < 14) {
                    $this->setError($this->data['text_error_whatsapp_business_account_id_format']);
                    return false;
                }
                
                if (!$this->secure->is_token($this->request->post['token'])) {
                    $this->setError($this->data['text_error_token_format']);
                    return false;
                }
            }
                
            if (!$this->secure->is_phone($this->request->post['phone_id'])) {
                $this->setError($this->data['text_error_phone_format']);
                return false;
            }
			
			$user_id = $this->request->get['user_id'] ?? $this->request->post['user_id'] ?? 0;
			
			if ($this->load_model('pages/settings', 'phoneExists', [$user_id, $this->request->post['phone_id']])) {
				$this->setError($this->data['text_error_phone_already_exists']);
				return false;
			}
			
			$extensions = $this->load_model('pages/extensions', 'getExtensions', ['captcha']);
            
            foreach($extensions as $extension) {
                $is_to_all_users = $this->config->get_all('extensions/captcha/' . $extension['code'])['to_all_users'];
                
                if ($this->model_pages_extensions->getConfigStatusExtension($extension['id'], $is_to_all_users) == '1') {
                    if (!$this->load_controller('extensions/captcha/' . $extension['code'], 'validate')) {
        				$this->setError($this->data['text_error_captcha']);
                        return false;
                    }
                }
            }
			
            return true;
        }
    }
    