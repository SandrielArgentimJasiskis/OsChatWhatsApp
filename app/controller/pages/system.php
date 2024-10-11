<?php
	
	namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
	
	class ControllerPagesSystem extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
	
		public function index() {
			$this->load_model('pages/system');
            
            if (!empty($this->request->post) && $this->validate()) {
                $system = $this->model_pages_system->get();
                
                /* Adiciona ao histórico a modificação feita pelo usuário */
                $this->load_controller('common/history', 'addHistory', ['update_system', json_encode($system)]);
                
				$this->model_pages_system->edit($this->request->post);
                
                $this->setMsg($this->data['text_msg_success']);
            }
            
            if ($this->getError()) {
                $this->data['error'] = $this->getError();
            }
            
            if ($this->getMsg()) {
                $this->data['msg'] = $this->getMsg();
            }
            
            $this->data['action'] = $this->url->link('pages/system/index');
			
			$this->data['languages'] = $this->model_pages_system->getLanguages();
			$this->data['themes'] = $this->model_pages_system->getThemes();
            
            $system = $this->model_pages_system->get();
            
            $this->data['default_language_id'] = $this->request->post['default_language_id'] ?? $system['default_language_id'];
            $this->data['default_theme_id'] = $this->request->post['default_theme_id'] ?? $system['default_theme_id'];
            $this->data['conversation_duration'] = $this->request->post['conversation_duration'] ?? $system['conversation_duration'];
            $this->data['api_domain'] = $this->request->post['api_domain'] ?? $system['api_domain'];
            $this->data['api_version'] = $this->request->post['api_version'] ?? $system['api_version'];
            $this->data['cron_token'] = $this->request->post['cron_token'] ?? $system['cron_token'];
            $this->data['mail_host'] = $this->request->post['mail_host'] ?? $system['mail_host'];
            $this->data['mail_username'] = $this->request->post['mail_username'] ?? $system['mail_username'];
            $this->data['mail_password'] = $this->request->post['mail_password'] ?? $system['mail_password'];
            $this->data['mail_encryption'] = $this->request->post['mail_encryption'] ?? $system['mail_encryption'];
            $this->data['mail_port'] = $this->request->post['mail_port'] ?? $system['mail_port'];
			
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
        
            $this->template->display($this->load_view('pages/system', $this->secure->remove_tags($this->data, '', ['header', 'footer', 'captcha'])));
        }
        
        public function validate() {
            if (!isset($this->request->post[$this->request->session['token']['name']])) {
                $this->secure->access_denied();
            }
            
            if ($this->request->post[$this->request->session['token']['name']] !== $this->request->session['token']['value']) {
                $this->secure->access_denied();
            }
            
            if (!$this->secure->is_int($this->request->post['default_language_id'])) {
                $this->setError($this->data['text_error_language_format']);
                return false;
            }
            
            if (!$this->secure->is_int($this->request->post['default_theme_id'])) {
                $this->setError($this->data['text_error_theme_format']);
                return false;
            }
            
            if (!$this->secure->is_conversation_duration($this->request->post['conversation_duration'])) {
                $this->setError($this->data['text_error_conversation_duration_format']);
                return false;
            }
            
            if ($this->request->post['api_domain'] == '') {
                $this->setError($this->data['text_error_api_domain_format']);
                return false;
            }
            
            if ($this->request->post['api_version'] == '') {
                $this->setError($this->data['text_error_api_version_format']);
                return false;
            }
            
            if (!$this->secure->is_token($this->request->post['cron_token'])) {
                $this->setError($this->data['text_error_cron_token_format']);
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
	