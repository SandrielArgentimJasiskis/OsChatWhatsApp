<?php
    
    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
    
    class ControllerPagesExtensions extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
        
        public function index() {
            $user_info = $this->load_model('pages/users', 'getUser', (array)$this->user->getId());
            
            $extensions = $this->getList();
            
            if ($extensions) {
                foreach($extensions as $extension) {
            		if ($this->secure->has_access('extensions/' . $extension['type'] . '/' . $extension['code'], 'access', $user_info)) {
            		    $this->data['extensions'][] = array(
                            'id'        => $extension['id'],
                            'type'      => $extension['type'],
                            'title'     => $this->load_language('extensions/' . $extension['type'] . '/' . $extension['code'])['text_title'],
    						'edit'		=> $this->url->link('pages/extensions/edit', '&extension_id=' . $extension['id']),
                            'status'    => $extension['status']
                        );
                    }
                }
            }
            
            if ($this->getError()) {
                $this->data['error'] = $this->getError();
            }
            
            $this->data['type'] = $this->request->get['type'] ?? 'dashboard';
            
            $this->data['link_captcha'] = $this->url->link('pages/extensions/index', '&type=captcha');
            $this->data['link_dashboard'] = $this->url->link('pages/extensions/index', '&type=dashboard');
            $this->data['link_installers'] = $this->url->link('pages/extensions/index', '&type=installers');
            $this->data['link_integrations'] = $this->url->link('pages/extensions/index', '&type=integrations');
            $this->data['link_login'] = $this->url->link('pages/extensions/index', '&type=login');
            $this->data['link_reports'] = $this->url->link('pages/extensions/index', '&type=reports');
            $this->data['link_cron'] = $this->url->link('pages/extensions/index', '&type=cron');
            $this->data['link_themes'] = $this->url->link('pages/extensions/index', '&type=themes');
        
            $this->template->display($this->load_view('pages/extensions', $this->secure->remove_tags($this->data, '', ['header', 'footer'])));
        }
        
        public function edit() {
            $extension_id = $this->request->get['extension_id'] ?? 0;
            
            $this->load_model('pages/extensions');
            
            if (!empty($this->request->post) && $this->validate()) {
                $extension = $this->model_pages_extensions->getExtensionById($extension_id);
                
                $is_to_all_users = $this->config->get_all('extensions/' . $extension['type'] . '/' . $extension['code'])['to_all_users'];
            
                $configs = $this->model_pages_extensions->getConfigExtension($extension_id, $is_to_all_users);
                
                /* Adiciona ao histórico a modificação feita pelo usuário */
                $this->load_controller('common/history', 'addHistory', ['update_extension', json_encode($configs)]);
                
                $this->model_pages_extensions->edit($extension_id, $this->request->post, $is_to_all_users);
                
                header('Content-Type: application/json; charset=utf-8');
                
                die(json_encode(['success'   => $this->data['text_success']]));
            }
            
            if ($this->getError()) {
                //header('content-type: text/html; charset=utf-8');
                
                header('Content-Type: application/json; charset=utf-8');
                
                die(json_encode(['error'    => $this->getError()]));
            }
            
            $extension = $this->model_pages_extensions->getExtensionById($extension_id);
            
            $is_to_all_users = $this->config->get_all('extensions/' . $extension['type'] . '/' . $extension['code'])['to_all_users'];
                
            
            $configs = $this->model_pages_extensions->getConfigExtension($extension_id, $is_to_all_users);
            
            $this->data = array_merge($this->data, $this->load_language('extensions/' . $extension['type'] . '/' . $extension['code']));
            
            foreach($configs as $key => $config) {
                $this->data[$config['config']] = $config['value'];
            }
			
            $this->data['url'] = $this->url->link('pages/extensions/edit', '&extension_id=' . $extension_id);
            
            /* Gera um token aleatório anti CSRF */
            $this->data['token_name'] = '_' . $this->secure->random(64);
            $this->data['token_value'] = $this->secure->random(64);
            $this->session->data('token', [
                'name'  => $this->data['token_name'], 
                'value' => $this->data['token_value']
            ]);
            
            $this->template->display($this->load_view('common/top_popup', $this->secure->remove_tags($this->data, '<i>')));
            
            $user_info = $this->load_model('pages/users', 'getUser', (array)$this->user->getId());
            
            if (!$this->secure->has_access('extensions/' . $extension['type'] . '/' . $extension['code'], 'access', $user_info)) {
                $data['error'] = $this->data['text_error_access_access'];
                
                $this->template->display($this->load_view('pages/error', $this->secure->remove_tags($data)));
            } else {
                $this->template->display($this->load_controller('extensions/' . $extension['type'] . '/' . $extension['code'], 'index', [$this->secure->remove_tags($this->data, '')]));
                
                $this->template->display($this->load_view('common/bottom_popup', $this->secure->remove_tags($this->data)));
            }
        }
        
        public function getList() {
            $extensions_type = $this->request->get['type'] ?? 'dashboard';
            
            return $this->load_model('pages/extensions', 'getExtensions', (array)$extensions_type);
        }
        
        private function validate() {
            if (!isset($this->request->post[$this->request->session['token']['name']])) {
                $this->secure->access_denied();
            }
            
            if ($this->request->post[$this->request->session['token']['name']] !== $this->request->session['token']['value']) {
                $this->secure->access_denied();
            }
            
            $extension_id = $this->request->get['extension_id'] ?? 0;
            
            $extension = $this->model_pages_extensions->getExtensionById($extension_id);
            
            $user_info = $this->load_model('pages/users', 'getUser', (array)$this->user->getId());
            
            if (!$this->secure->has_access('extensions/' . $extension['type'] . '/' . $extension['code'], 'modify', $user_info)) {
                $this->setError($this->data['text_error_access_modify']);
                return false;
            }
            
            $is_to_all_users = $this->config->get_all('extensions/' . $extension['type'] . '/' . $extension['code'])['to_all_users'];
            
            $configs = $this->model_pages_extensions->getConfigExtension($extension_id, $is_to_all_users);
            
            $fields = $this->config->get_fields('extensions/' . $extension['type'] . '/' . $extension['code']);
            
            if (!empty($fields)) {
                foreach($fields as $field) {
                    if ($field['required'] && (!isset($this->request->post[$field['field']]))) {
                        $this->log->write('Field invalid not sent: ' . $this->request->post[$field['field']]);
                        
                        $this->setError($this->data['text_error_all_fields_format']);
                        return false;
                    }
                    
                    if ($field['regex']) {
                        if (!preg_match($field['regex'], $this->request->post[$field['field']])) {
                            $this->log->write('Field invalid: ' . $this->request->post[$field['field']]);
                            
                            $this->setError($this->data['text_error_all_fields_format']);
                            return false;
                        }
                    }
                }
            }
            
            $settings = $this->config->get_all('extensions/' . $extension['type'] . '/' . $extension['code']);
            if (!empty($settings['validate'])) {
                $this->log->write('metodo validate encontrado!');
                
                if ($settings['validate']['status']) {
                    $controller = $settings['validate']['controller'];
                    $method = $settings['validate']['method'];
                    
                    $data = array(
                        'extension_id' => $extension_id,
                        'extension' => $extension,
                        'configs' => $configs,
                        'fields' => $fields
                    );
                    
                    if ($controller == 'pages/extensions') {
                        $this->log->write('Controller identificado como ele mesmo!');
                        $this->log->write('Resultado da verificação das extensões do tipo único: ' . print_r($this->$method($data), true));
                        if ($this->$method($data)) {
                            $this->log->write('Mais de uma extensão com o status habilitado!');
                            $this->setError($this->data['text_error_extension_type_status_exists']);
                            return false;
                        }
                    }
                }
            }
            
            return true;
        }
        
        
        public function validateUniqueExensionStatusType($data) {
            $this->load_model('pages/extensions');
            
            return $this->model_pages_extensions->extensionTypeStatusExists($data['extension']['type'], $data['extension_id']);
        }
    }
    