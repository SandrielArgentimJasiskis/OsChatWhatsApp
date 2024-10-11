<?php
    
    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
    
    class ControllerPagesUsers extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
        
        public function index() {
            $users = $this->getList();
            
            foreach($users as $user) {
                $this->data['users'][] = array(
                    'id'                => $user['id'],
                    'fullname'          => $user['fullname'],
                    'user'              => $user['user'],
                    'view'              => $this->url->link('pages/users/view', '&user_id=' . $user['id']),
                    'edit'              => $this->url->link('pages/users/edit', '&user_id=' . $user['id']),
                    'delete'            => $user['id'],
                    'question_delete'   => sprintf($this->data['text_question_delete'], $user['fullname'])
                );
            }
            
            $this->data['add'] = $this->url->link('pages/users/add');
            
            if (!empty($this->request->session['error'])) {
                $this->data['error'] = $this->request->session['error'];
                
                $this->session->destroy('error');
            }
            if (!empty($this->request->session['msg'])) {
                $this->data['msg'] = $this->request->session['msg'];
                
                $this->session->destroy('msg');
            }
            
            $this->data['url'] = $this->url->link('pages/users/delete');
        
            $this->template->display($this->load_view('pages/users_list', $this->secure->remove_tags($this->data, '<i>', ['header', 'footer'])));
        }
        
        public function add() {
            if (!empty($this->request->post)) {
                $this->request->post['pass_required'] = true;
            }
            
            if (!empty($this->request->post) && $this->validate() && $this->load_controller('pages/settings', 'validate')) {
                $this->load_model('pages/users', 'add',
                (array)json_encode($this->request->post));
                
                $this->session->data('msg', $this->data['text_msg_success_add']);
                
                $this->url->redirect('pages/users/index');
            }
            
            if ($this->getError()) {
                $this->data['error'] = $this->getError();
            } elseif (!empty($this->controller_pages_settings)) {
                if ($this->controller_pages_settings->getError()) {
                    $this->data['error'] = $this->controller_pages_settings->getError();
                }
            }
            
            $this->data['access_area'] = $this->secure->get_access_area();
            
            $this->data['url'] = $this->url->link('pages/users/getPhones');
            
            $this->data['action'] = $this->url->link('pages/users/add');
            
            $this->data['title'] = $this->data['text_title_add'];
            $this->data['send'] = $this->data['text_button_submit_add'];
            
            $this->data['user'] = $this->request->post['user'] ?? '';
            $this->data['fullname'] = $this->request->post['fullname'] ?? '';
            $this->data['app_id'] = $this->request->post['app_id'] ?? '';
            $this->data['whatsapp_business_account_id'] = $this->request->post['whatsapp_business_account_id'] ?? '';
            $this->data['phone_id'] = $this->request->post['phone_id'] ?? '';
            $this->data['token'] = $this->request->post['token'] ?? '';
            $this->data['access'] = $this->request->post['access'] ?? array();
            $this->data['limitations'] = $this->request->post['limitations'] ?? array();
            
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
        
            $this->template->display($this->load_view('pages/users_form', $this->secure->remove_tags($this->data, '', ['header', 'footer', 'captcha'])));
        }
        
        public function edit() {
            if (!empty($this->request->post) && $this->validate() && $this->load_controller('pages/settings', 'validate')) {
                if (empty($this->request->get['user_id'])) {
                    $this->url->redirect('pages/pages/users/add');
                }
                
                $this->request->post['id'] = $this->request->get['user_id'];
                
                $this->load_model('pages/users', 'edit',
                (array)json_encode($this->request->post));
                
                $this->session->data('msg', $this->data['text_msg_success_edit']);
                
                $this->url->redirect('pages/users/index');
            }
            
            if ($this->getError()) {
                $this->data['error'] = $this->getError();
            } elseif (!empty($this->controller_pages_settings)) {
                if ($this->controller_pages_settings->getError()) {
                    $this->data['error'] = $this->controller_pages_settings->getError();
                }
            }
            
            $this->data['access_area'] = $this->secure->get_access_area();
            
            $this->data['url'] = $this->url->link('pages/users/getPhones');
            
            $this->data['action'] = $this->url->link('pages/users/edit', '&user_id=' . $this->request->get['user_id']);
            
            $this->data['title'] = $this->data['text_title_edit'];
            $this->data['send'] = $this->data['text_button_submit_edit'];
            
            $user_id[0] = $this->request->get['user_id'];
            
            $user_info = $this->load_model('pages/users', 'getUser', $user_id);
            
            $this->data['user'] = $this->request->post['user'] ?? $user_info['user'] ?? '';
            $this->data['fullname'] = $this->request->post['fullname'] ?? $user_info['fullname'] ?? '';
            
            $this->data['access'] = $this->request->post['access'] ?? json_decode($user_info['access'], true);
            $this->data['limitations'] = $this->request->post['limitations'] ?? json_decode($user_info['limitations']);
            
            $this->data['access']['access'] = $this->data['access']['access'] ?? array();
            $this->data['access']['modify'] = $this->data['access']['modify'] ?? array();
              
            $settings = $this->load_model('pages/settings', 'getSettings', $user_id);
            
            $this->data['app_id'] = $this->request->post['app_id'] ?? $settings['app_id'];
            $this->data['whatsapp_business_account_id'] = $this->request->post['whatsapp_business_account_id'] ?? $settings['whatsapp_business_account_id'];
            $this->data['phone_id'] = $this->request->post['phone_id'] ?? $settings['phone_id'];
            $this->data['token'] = $this->request->post['token'] ?? $settings['token'];
              
            $this->data['phone_numbers'] = $this->load_controller('pages/settings', 'getPhones', [$this->data]);
            
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
            
            $this->template->display($this->load_view('pages/users_form', $this->secure->remove_tags($this->data, '', ['header', 'footer', 'captcha'])));
        }
        
        public function view() {
            if (empty($this->request->get['user_id'])) {
                $this->url->redirect('pages/users/add');
            }
            
            $this->session->data('user_use_id', $this->secure->to_int($this->request->get['user_id']));
            $this->url->redirect('pages/dashboard');
        }
        
        public function delete() {
            if (!empty($this->request->post)) {
                $this->session->data('msg', $this->data['text_msg_success_delete']);
                
                $this->load_model('pages/users', 'delete', (array)$this->request->post['user_id']);
                
                $this->url->redirect('pages/users/index');
            }
            
            if ($this->getError()) {
                $this->session->data('error', $this->getError());
                
                $this->index();
            }
        }
        
        public function getList() {
            return $this->load_model('pages/users', 'getAllUsers');
        }
        
        public function getPhones() {
            $request = $this->request->post;
            
            if (empty($request['app_id'])) {
                die();
            }
            
            if (empty($request['whatsapp_business_account_id'])) {
                die();
            }
            
            if (empty($request['token'])) {
                die();
            }
            
            $this->data['phone_numbers'] = $this->load_controller('pages/settings', 'getPhones', [$request]);
            
            $this->template->display($this->load_view('pages/phones_json', $this->secure->remove_tags($this->data)));
        }
        
        private function validate() {
            if (!isset($this->request->post[$this->request->session['token']['name']])) {
                $this->secure->access_denied();
            }
            
            if ($this->request->post[$this->request->session['token']['name']] !== $this->request->session['token']['value']) {
                $this->secure->access_denied();
            }
            
            if (!$this->secure->is_user($this->request->post['user'])) {
                $this->setError($this->data['text_error_username_format']);
                return false;
            }
            
            
            if (!$this->secure->is_fullname($this->request->post['fullname'])) {
                $this->setError($this->data['text_error_fullname_format']);
                return false;
            }
            
			if (($this->request->post['pass'] != '' || $this->request->post['confirm']) || !empty($this->request->post['pass_required'])) {
				if (!$this->secure->is_pass($this->request->post['pass'])) {
					$this->setError($this->data['text_error_pass_format']);
					return false;
				}
				if ($this->request->post['pass'] != $this->request->post['confirm']) {
					$this->setError($this->data['text_error_confirm_format']);
					return false;
				}
			}
			
			$user_id = $this->request->get['user_id'] ?? 0;
			
			if ($this->load_model('pages/users', 'userExists', [$user_id, $this->request->post['user']])) {
				$this->setError($this->data['text_error_user_already_exists']);
				return false;
			}
            
            return true;
        }
    }
    