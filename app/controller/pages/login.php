<?php

    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;

    class ControllerPagesLogin extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
        
        public function index() {
            if (!empty($this->request->post) && $this->validate()) {
                $this->load_model('pages/login');
                $login = $this->model_pages_login->login($this->request->post['user'], $this->request->post['pass']);
                
                if ($login) {
                    $this->session->data('user_id', $login);
                    
                    $this->load_controller('common/history', 'addHistory', ['login', array(), $login]);
                    
                    if ($this->request->session['redirect'] == '') {
                        $this->request->session['redirect'] = 'pages/dashboard/index';
                    }
                    
                    $this->url->redirect($this->request->session['redirect']);
                }
            }
            
            if ($this->getError()) {
                $this->data['error'] = $this->getError();
            }
            
            if (!empty($this->request->session['msg'])) {
                $this->data['msg'] = $this->request->session['msg'];
                $this->session->destroy('msg');
            }
            
            $this->data['action'] = $this->url->link('pages/login/index');
            
            $this->data['forgotten'] = $this->url->link('pages/forgotten/index');
            
            $this->data['user'] = $this->request->post['user'] ?? '';
			
			$this->load_model('pages/extensions');
		    
		    $extensions = $this->load_model('pages/extensions', 'getExtensions', ['captcha']);
            
            $this->data['extensions'] = array();
            foreach($extensions as $extension) {
                $is_to_all_users = $this->config->get_all('extensions/captcha/' . $extension['code'])['to_all_users'];
                
                if ($this->model_pages_extensions->getConfigStatusExtension($extension['id'], $is_to_all_users) == '1') {
                    $this->data['captcha'] = $this->load_captcha();
                }
            }
            
            /* Gera um token aleatório anti CSRF */
            $this->data['token_name'] = '_' . $this->secure->random(64);
            $this->data['token_value'] = $this->secure->random(64);
            $this->session->data('token', [
                'name'  => $this->data['token_name'], 
                'value' => $this->data['token_value']
            ]);
            
            $this->template->display($this->load_view('pages/login', $this->secure->remove_tags($this->data, '', ['header', 'footer', 'captcha'])));
        }
        
		private function get_captcha() {
		    $this->load_model('pages/extensions');
		    
		    $extensions = $this->load_model('pages/extensions', 'getExtensions', ['captcha']);
            
            $data['extensions'] = array();
            foreach($extensions as $extension) {
                $is_to_all_users = $this->config->get_all('extensions/captcha/' . $extension['code'])['to_all_users'];
                if ($this->model_pages_extensions->getConfigStatusExtension($extension['id'], $is_to_all_users) == '1') {
                    return 'extensions/captcha/' . $extension['code'];
                }
            }
            
            return false;
		}
		
		public function load_captcha() {
		    $captcha = $this->get_captcha();
		    
		    return ($captcha) ? $this->load_controller($captcha, 'captcha', []) : false;
		}
		
				
		public function generate_captcha() {
		    $captcha = $this->get_captcha();
		    
		    return ($captcha) ? $this->load_controller($captcha, 'generate_captcha', []) : false;
		}
        
        private function validate() {
            if (!isset($this->request->post[$this->request->session['token']['name']]) || !isset($this->request->post['user']) || !isset($this->request->post['pass'])) {
                $this->secure->access_denied();
            }
            
            if ($this->request->post[$this->request->session['token']['name']] !== $this->request->session['token']['value']) {
                $this->secure->access_denied();
            }
            
            if (!$this->secure->is_user($this->request->post['user']) || !$this->secure->is_pass($this->request->post['pass'])) {
                $this->setError($this->data['text_error_login']);
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