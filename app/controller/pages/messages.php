<?php
    
    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
    
    class ControllerPagesMessages extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
        
        public function index() {
            $messages = $this->getList();
            
            if ($messages) {
                foreach($messages as $message) {
                    $this->data['messages'][] = array(
                        'id'        => $message['id'],
                        'message_title'     => $message['message_title'],
                        'type'      => $this->getType($message['type']),
                        'edit'      => $this->url->link('pages/messages/edit', '&message_id=' . $message['id']),
                        'delete'    => $message['id'],
                        'question_delete'   => sprintf($this->data['text_question_delete'], $message['message_title'])
                    );
                }
            }
            
            $this->data['add'] = $this->url->link('pages/messages/add');
            
            if (!empty($this->request->session['error'])) {
                $this->data['error'] = $this->request->session['error'];
                
                $this->session->destroy('error');
            }
            if (!empty($this->request->session['msg'])) {
                $this->data['msg'] = $this->request->session['msg'];
                
                $this->session->destroy('msg');
            }
            
            $this->data['url'] = $this->url->link('pages/messages/delete');
        
            $this->template->display($this->load_view('pages/messages_list', $this->secure->remove_tags($this->data, '<i>', ['header', 'footer'])));
        }
        
        public function add() {
            if (!empty($this->request->post) && $this->validate()) {
                $this->load_model('pages/messages', 'add',
                (array)json_encode($this->request->post));
                
                /* Adiciona ao histórico a modificação feita pelo usuário */
                $this->load_controller('common/history', 'addHistory', ['register_message', json_encode($this->request->post)]);
                
                $this->session->data('msg', $this->data['text_msg_success_add']);
                
                $this->url->redirect('pages/messages/index');
            }
            
            if ($this->getError()) {
                $this->data['error'] = $this->getError();
            }
            
            $messages = $this->getList();
            
            if ($messages) {
                foreach($messages as $message) {
                    if ($message['type'] == 'interactive') {
                        $this->data['messages'][] = array(
                            'id'        => $message['id'],
                            'message_title'     => $message['message_title']
                        );
                    }
                }
            }
            
            $this->data['templates'] = $this->getTemplates();
            
            $this->data['action'] = $this->url->link('pages/messages/add');
            
            $this->data['title'] = $this->data['text_title_add'];
            $this->data['send'] = $this->data['text_button_submit_add'];
            
            $this->data['status'] = $this->request->post['status'] ?? 0;
            $this->data['message_title'] = $this->request->post['message_title'] ?? '';
            $this->data['is_init'] = $this->request->post['is_init'] ?? 0;
            $this->data['is_self'] = $this->request->post['is_self'] ?? '';
            $this->data['attendant_id'] = $this->request->post['attendant_id'] ?? 0;
            $this->data['finish'] = $this->request->post['finish'] ?? 0;
            $this->data['type'] = $this->request->post['type'] ?? '';
            $this->data['message_options'] = $this->request->post['message_option'] ?? array();
            $this->data['event'] = $this->request->post['event'] ?? '';
            $this->data['message_responses'] = $this->request->post['message_responses'] ?? array();
            $this->data['message_keyword'] = $this->request->post['message_keyword'] ?? '';
            $this->data['message_content'] = $this->request->post['message_content'] ?? array();
            
            if (!empty($this->data['message_content']['template'])) {
                $this->data['message_content']['template']['id'] = explode("|", $this->data['message_content']['template']['id'])[0];
            }
            
			$this->data['url_get_message'] = $this->url->link('pages/messages/getMessage');
            $this->data['url_get_all_attendants'] = $this->url->link('pages/attendants/getAll');
            
            /* Gera um token aleatório anti CSRF */
            $this->data['token_name'] = '_' . $this->secure->random(64);
            $this->data['token_value'] = $this->secure->random(64);
            $this->session->data('token', [
                'name'  => $this->data['token_name'], 
                'value' => $this->data['token_value']
            ]);
        
            $this->template->display($this->load_view('pages/messages_form', $this->secure->remove_tags($this->data, '', ['header', 'footer'])));
        }
        
        public function edit() {
            if (!empty($this->request->post) && $this->validate()) {
                if (empty($this->request->get['message_id'])) {
                    $this->url->redirect('pages/messages/add');
                }
                
                $this->request->post['id'] = $this->request->get['message_id'];
                
                $message_info = $this->load_model('pages/messages', 'getMessage', (array)$this->request->post['id']);
                
                /* Adiciona ao histórico a modificação feita pelo usuário */
                $this->load_controller('common/history', 'addHistory', ['update_message', json_encode($message_info)]);
                
                $this->load_model('pages/messages', 'edit',
                (array)json_encode($this->request->post));
                
                $this->session->data('msg', $this->data['text_msg_success_edit']);
                
                $this->url->redirect('pages/messages/index');
            }
            
            if ($this->getError()) {
                $this->data['error'] = $this->getError();
            } elseif (!empty($this->controller_pages_settings)) {
                if ($this->controller_pages_settings->getError()) {
                    $this->data['error'] = $this->controller_pages_settings->getError();
                }
            }
            
            $this->data['access_area'] = $this->secure->get_access_area();
            
            $messages = $this->getList();
            
            if ($messages) {
                foreach($this->getList() as $message) {
                    if ($message['type'] == 'interactive') {
                        $this->data['messages'][] = array(
                            'id'        => $message['id'],
                            'message_title'     => $message['message_title']
                        );
                    }
                }
            }
            
            $this->data['templates'] = $this->getTemplates();
            
            $this->data['action'] = $this->url->link('pages/messages/edit', '&message_id=' . $this->request->get['message_id']);
            
            $this->data['title'] = $this->data['text_title_edit'];
            $this->data['send'] = $this->data['text_button_submit_edit'];
            
            $message_id = $this->request->get['message_id'];
            
            $message_info = $this->load_model('pages/messages', 'getMessage', (array)$message_id);
            if (!$message_info['message']) {
                $this->url->redirect('pages/messages/add');
            }
            
            $this->data['message_id'] = $message_id;
                        
            $this->data['status'] = $this->request->post['status'] ?? $message_info['message']['status'] ?? 0;
            $this->data['message_title'] = $this->request->post['message_title'] ?? $message_info['message']['message_title'] ?? '';
            $this->data['is_init'] = $this->request->post['is_init'] ?? $message_info['message']['is_init'] ?? 0;
            $this->data['is_self'] = $this->request->post['is_self'] ?? $message_info['message']['is_self'] ?? '';
            $this->data['attendant_id'] = $this->request->post['attendant_id'] ?? $message_info['message']['attendant_id'] ?? 0;
            $this->data['finish'] = $this->request->post['finish'] ?? $message_info['message']['finish'] ?? 0;
            $this->data['type'] = $this->request->post['type'] ?? $message_info['message']['type'] ?? 'text';
            $this->data['message_options'] = $this->request->post['message_option'] ?? $message_info['options'];
            $this->data['event'] = $this->request->post['event'] ?? $message_info['message']['event'];
            $this->data['message_responses'] = $this->request->post['message_responses'] ?? $message_info['responses'];
            $this->data['message_keyword'] = $this->request->post['message_keyword'] ?? $message_info['keyword'];
            $this->data['message_content'] = $this->request->post['content'] ?? json_decode($message_info['message']['message_content'], true);
            
            if (!empty($this->data['message_content']['template'])) {
                $this->data['message_content']['template']['id'] = explode("|", $this->data['message_content']['template']['id'])[0];
            }
            
            $this->data['url_get_message'] = $this->url->link('pages/messages/getMessage');
            $this->data['url_get_all_attendants'] = $this->url->link('pages/attendants/getAll');
            
            /* Gera um token aleatório anti CSRF */
            $this->data['token_name'] = '_' . $this->secure->random(64);
            $this->data['token_value'] = $this->secure->random(64);
            $this->session->data('token', [
                'name'  => $this->data['token_name'], 
                'value' => $this->data['token_value']
            ]);
            
            $this->template->display($this->load_view('pages/messages_form', $this->secure->remove_tags($this->data, '', ['header', 'footer'])));
        }
        
        public function delete() {
            if (!empty($this->request->post)) {
                $this->session->data('msg', $this->data['text_msg_success_delete']);
                
                $this->load_model('pages/messages', 'delete', (array)$this->request->post['message_id']);
                
                $this->url->redirect('pages/messages/index');
            }
            
            if ($this->getError()) {
                $this->session->data('error', $this->getError());
                
                $this->index();
            }
        }
        
        public function getMessage() {
            $message = $this->load_model('pages/messages', 'getMessage', (array)$this->request->get['message_id']);
            
            $this->data['message'] = $message;
            
            $this->template->display($this->load_view('pages/message_json', $this->data));
        }
        
        public function getTemplates() {
            $this->load_model('pages/system');
            
            $system = $this->model_pages_system->get();
            $this->data['api_version'] = $system['api_version'];
            
            $settings = $this->load_model('pages/settings', 'getSettings', (array)$this->user->getId());
            
            $this->data['app_id'] = $this->request->post['app_id'] ?? $settings['app_id'];
            $this->data['whatsapp_business_account_id'] = $this->request->post['whatsapp_business_account_id'] ?? $settings['whatsapp_business_account_id'];
            $this->data['phone_id'] = $this->request->post['phone_id'] ?? $settings['phone_id'];
            $this->data['token'] = $this->request->post['token'] ?? $settings['token'];
            
            return $this->getAllTemplates($this->data);
        }
        
        private function getAllTemplates($data) {
            $system = $this->model_pages_system->get();
            
            $ch = curl_init();
        
            curl_setopt($ch, CURLOPT_URL, 'https://' . $this->secure->clear($system['api_domain']) . '/' . $this->secure->clear($data['api_version']) . '/' . $this->secure->clear($data['whatsapp_business_account_id']) . '?fields=message_templates');
            
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
            
            return $result['message_templates']['data'] ?? false;
        }
        
        public function getTemplateContent($template_id, $token) {
            $this->load_model('pages/system');
            $this->load_model('webhook/settings');
            
            $system = $this->model_pages_system->get();
            
            $ch = curl_init();
            
            $this->log->write('URL da API: ' . 'https://' . $this->secure->clear($system['api_domain']) . '/' . $this->secure->clear($system['api_version']) . '/' . $this->secure->clear($template_id));
        
            curl_setopt($ch, CURLOPT_URL, 'https://' . $this->secure->clear($system['api_domain']) . '/' . $this->secure->clear($system['api_version']) . '/' . $this->secure->clear($template_id));
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            
            $headers = array();
            $headers[] = 'Authorization: Bearer ' . $this->secure->clear($token);
            $headers[] = 'Content-Type: application/json';
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                $this->log->write('Erro ao buscar o template: ' . print_r(curl_errno($ch)));
                return curl_errno($ch);
            }
            
            curl_close($ch);
            
            $result = json_decode($result, true);
            
            $this->log->write('Conteudo do template buscado: ' . print_r($result, true));
            
            return (isset($result['name'])) ? $result : false;
        }
        
        public function getType($message) {
            $message_types = array(
                'text'          => $this->data['text_message_type_text'],
                'image'         => $this->data['text_message_type_image'],
                'interactive'   => $this->data['text_message_type_interactive'],
                'media'         => $this->data['text_message_type_media'],
                'template'      => $this->data['text_message_type_template']
            );
            
            return (!empty($message_types[$message])) ? $message_types[$message] : false;
        }
        
        public function getList() {
            return $this->load_model('pages/messages', 'getUserMessages', (array)$this->user->getId());
        }
        
        private function validate() {
            if (!isset($this->request->post[$this->request->session['token']['name']])) {
                $this->secure->access_denied();
            }
            
            if ($this->request->post[$this->request->session['token']['name']] !== $this->request->session['token']['value']) {
                $this->secure->access_denied();
            }
            
            if (!$this->secure->is_message_title($this->request->post['message_title'])) {
                $this->setError($this->data['text_error_message_title_format']);
                return false;
            }
            
            if ((strpos($this->request->post['message_title'], '[customer_name]') !== false) || (strpos($this->request->post['message_title'], '[attendant_name]') !== false)) {
                $this->setError($this->data['text_error_message_title_use_var']);
                        return false;
            }
            
            if (!$this->secure->is_message_type($this->request->post['type'])) {
                $this->setError($this->data['text_error_message_type_format']);
                return false;
            }
            
            if (!$this->secure->is_message_event($this->request->post['event'])) {
                $this->setError($this->data['text_error_message_event_format']);
                return false;
            }
            
            if (!empty($this->request->post['message_option'])) {
                foreach($this->request->post['message_option'] as $option) {
                    if (!$this->secure->is_message_option_title($option['option_title'])) {
                        $this->setError($this->data['text_error_message_option_title_format']);
                        return false;
                    }
                    /*if (!$this->secure->is_message_option_description($option['option_description'])) {
                        $this->setError($this->data['text_error_message_option_description_format']);
                        return false;
                    }*/
                    
                    if ((strpos($option['option_title'], '[customer_name]') !== false) || (strpos($option['option_title'], '[attendant_name]') !== false) || (strpos($option['option_description'], '[customer_name]') !== false) || (strpos($option['option_description'], '[attendant_name]') !== false)) {
                        $this->setError($this->data['text_error_message_option_use_var']);
                        return false;
                    }
                }
            }
            
            if ((strpos($this->request->post['message_keyword'], '[customer_name]') !== false) || (strpos($this->request->post['message_keyword'], '[attendant_name]') !== false)) {
                $this->setError($this->data['text_error_keyword_use_var']);
                        return false;
            }
            
            if ($this->request->post['is_self'] == '1') {
                $this->request->post['attendant_id'] == '';
            }
            
            if (!empty($this->request->post['is_init'])) {
                if ($this->request->post['is_init'] == 'on' && $this->request->post['attendant_id'] != '') {
                    $this->setError($this->data['text_error_message_event_is_self_format']);
                    return false;
                }
                
                if ($this->request->post['is_init'] == 'on' && $this->request->post['finish'] == '1') {
                    $this->setError($this->data['text_error_finish_in_init']);
                    return false;
                }
            }
            
            if (in_array($this->request->post['event'], ['started_by_attendant', 'finished_by_attendant', 'finished_by_customer', 'timeout']) && $this->request->post['attendant_id'] != '') {
                $this->setError($this->data['text_error_message_event_is_self_format']);
                return false;
            }
            
            if (in_array($this->request->post['type'], ['interactive']) && in_array($this->request->post['event'], ['started_by_attendant', 'finished_by_attendant', 'finished_by_customer', 'timeout'])) {
                $this->setError($this->data['text_error_message_event_menu_format']);
                return false;
            }
            
            if (in_array($this->request->post['type'], ['media'])) {
                if (!$this->secure->is_message_media_type($this->request->post['message_content']['type'])) {
                    $this->setError($this->data['text_error_message_media_type_format']);
                    return false;
                }
                
                if (!$this->secure->is_url($this->request->post['message_content']['url'])) {
                    $this->setError($this->data['text_error_message_media_url_format']);
                    return false;
                }
            }
            
            if (strlen($this->request->post['message_content']['content']) > 2056) {
                $this->setError($this->data['text_error_message_content_size_format']);
                return false;
            }
            
            $this->load_model('pages/users');
            $user_info = $this->model_pages_users->getUser($this->user->getId());
            
            $this->load_model('pages/messages');
            
            $message_id = $this->request->get['message_id'] ?? 0;
            
            if (!empty($this->request->post['is_init'])) {
                if ($this->model_pages_messages->messageInitExists($this->user->getId(), $message_id) == '1') {
                    $this->setError($this->data['text_error_init_message_already_exists']);
                    return false;
                }
            }
            
            $limitations = json_decode($user_info['limitations'], true);
            
            $total = $this->model_pages_messages->getTotalUserMessages($this->user->getId());
            
            if ($this->secure->to_int($limitations['messages']) <= $total && $this->secure->to_int($limitations['messages']) != 0) {
                $this->setError($this->data['text_error_limitation_messages']);
                return false;
            }
            
            return true;
        }
    }
    