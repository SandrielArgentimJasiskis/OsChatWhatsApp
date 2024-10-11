<?php
    
    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
    
    class ControllerPagesAttendants extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
        
        public function index() {
            $attendants = $this->getList();
            
            if ($attendants) {
                foreach($attendants as $attendant) {
                    $this->data['attendants'][] = array(
                        'id'        => $attendant['id'],
                        'attendant_name'    => $attendant['name'],
                        'attendant_number'  => $attendant['number'],
                        'edit'              => $this->url->link('pages/attendants/edit', '&attendant_id=' . $attendant['id']),
                        'delete'            => $attendant['id'],
                        'question_delete'   => sprintf($this->data['text_question_delete'], $attendant['name'])
                    );
                }
            }
            
            $this->data['add'] = $this->url->link('pages/attendants/add');
            
            if (!empty($this->request->session['error'])) {
                $this->data['error'] = $this->request->session['error'];
                
                $this->session->destroy('error');
            }
            if (!empty($this->request->session['msg'])) {
                $this->data['msg'] = $this->request->session['msg'];
                
                $this->session->destroy('msg');
            }
            
            $this->data['url'] = $this->url->link('pages/attendants/delete');
        
            $this->template->display($this->load_view('pages/attendants_list', $this->secure->remove_tags($this->data, '<i>', ['header', 'footer'])));
        }
        
        public function add() {
            if (!empty($this->request->post) && $this->validate()) {
                $last_id = $this->load_model('pages/attendants', 'add',
                (array)json_encode($this->request->post));
                
                $content = $this->request->post;
                $content['registration_id'] = $last_id;
                
                /* Adiciona ao histórico a modificação feita pelo usuário */
                $this->load_controller('common/history', 'addHistory', ['register_attendant', json_encode($content)]);
                
                $this->session->data('msg', $this->data['text_msg_success_add']);
                
                $this->url->redirect('pages/attendants/index');
            }
            
            if ($this->getError()) {
                $this->data['error'] = $this->getError();
            }
            
            $attendants = $this->getList();
            
            if ($attendants) {
                foreach($this->getList() as $attendant) {
                    $this->data['attendants'][] = array(
                        'id'        => $attendant['id'],
                        'attendant_name'     => $attendant['name'],
                        'attendant_number'      => $attendant['number'],
                        'edit'      => $this->url->link('pages/attendants/edit', '&attendant_id=' . $attendant['id']),
                        'delete'    => $this->url->link('pages/attendants/delete', '&attendant_id=' . $attendant['id'])
                    );
                }
            }
            
            $this->data['action'] = $this->url->link('pages/attendants/add');
            
            $this->data['title'] = $this->data['text_title_add'];
            $this->data['send'] = $this->data['text_button_submit_add'];
            
            $this->data['attendant_name'] = $this->request->post['attendant_name'] ?? '';
            $this->data['attendant_number'] = $this->request->post['attendant_number'] ?? '';
            
            $this->template->display($this->load_view('pages/attendants_form', $this->secure->remove_tags($this->data, '', ['header', 'footer'])));
        }
        
        public function edit() {
            if (!empty($this->request->post) && $this->validate()) {
                if (empty($this->request->get['attendant_id'])) {
                    $this->url->redirect('pages/attendants/add');
                }
                
                $this->request->post['id'] = $this->request->get['attendant_id'];
                
                $this->load_model('pages/attendants');
                
                $attendant_info = $this->model_pages_attendants->getAttendant($this->request->get['attendant_id']);
                
                /* Adiciona ao histórico a modificação feita pelo usuário */
                $this->load_controller('common/history', 'addHistory', ['update_attendant', json_encode($attendant_info)]);
                
                $this->model_pages_attendants->edit(json_encode($this->request->post));
                
                $this->session->data('msg', $this->data['text_msg_success_edit']);
                
                $this->url->redirect('pages/attendants/index');
            }
            
            if ($this->getError()) {
                $this->data['error'] = $this->getError();
            } elseif (!empty($this->controller_pages_settings)) {
                if ($this->controller_pages_settings->getError()) {
                    $this->data['error'] = $this->controller_pages_settings->getError();
                }
            }
            
            $attendants = $this->getList();
            
            if ($attendants) {
                foreach($this->getList() as $attendant) {
                    $this->data['attendants'][] = array(
                        'id'        => $attendant['id'],
                        'attendant_name'     => $attendant['name'],
                        'attendant_number'      => $attendant['number'],
                        'edit'      => $this->url->link('pages/attendants/edit', '&attendant_id=' . $attendant['id']),
                        'delete'    => $this->url->link('pages/attendants/delete', '&attendant_id=' . $attendant['id'])
                    );
                }
            }
            
            $this->data['access_area'] = $this->secure->get_access_area();
            
            $this->data['action'] = $this->url->link('pages/attendants/edit', '&attendant_id=' . $this->request->get['attendant_id']);
            
            $this->data['title'] = $this->data['text_title_edit'];
            $this->data['send'] = $this->data['text_button_submit_edit'];
            
            $attendant_id = $this->request->get['attendant_id'];
            
            $attendant_info = $this->load_model('pages/attendants', 'getAttendant', (array)$attendant_id);
            if (!$attendant_info) {
                $this->url->redirect('pages/attendants/add');
            }
            
            $this->data['attendant_id'] = $attendant_id;
            $this->data['attendant_name'] = $this->request->post['attendant_name'] ?? $attendant_info['name'];
            $this->data['attendant_number'] = $this->request->post['attendant_number'] ?? $attendant_info['number'];
            
            $this->data['url'] = $this->url->link('pages/attendants/getAttendant');
            
            $this->template->display($this->load_view('pages/attendants_form', $this->secure->remove_tags($this->data, '', ['header', 'footer'])));
        }
        
        public function delete() {
            if (!empty($this->request->post)) {
                $this->session->data('msg', $this->data['text_msg_success_delete']);
                
                $this->load_model('pages/attendants', 'delete', (array)$this->request->post['attendant_id']);
                
                $this->url->redirect('pages/attendants/index');
            }
            
            if ($this->getError()) {
                $this->session->data('error', $this->getError());
                
                $this->index();
            }
        }
        
        public function getAll() {
            $this->data = array_merge($this->data, $this->load_language('pages/messages'));
            
            $this->data['attendants'] = $this->load_model('pages/attendants', 'getAllUserAttendants', (array)$this->user->getId());
            
            $this->template->display($this->load_view('pages/attendants_json', $this->secure->remove_tags($this->data)));
        }
        
        public function getList() {
            return $this->load_model('pages/attendants', 'getUserAttendants', (array)$this->user->getId());
        }
        
        private function validate() {
            if (!$this->secure->is_attendant_name($this->request->post['attendant_name'])) {
                $this->setError($this->data['text_error_attendant_name_format']);
                return false;
            }
            
            if ((strpos($this->request->post['attendant_name'], '[customer_name]') !== false) || (strpos($this->request->post['attendant_name'], '[attendant_name]') !== false)) {
                $this->setError($this->data['text_error_attendant_name_use_var']);
                        return false;
            }
            
            if (!$this->secure->is_phone($this->request->post['attendant_number'])) {
                $this->setError($this->data['text_error_phone_format']);
                return false;
            }
            
            $this->load_model('pages/users');
            $user_info = $this->model_pages_users->getUser($this->user->getId());
            
            $limitations = json_decode($user_info['limitations'], true);
            
            $this->load_model('pages/attendants');
            
            $action = explode('/', $this->request->get['route'])[1];
            
            $attendant_exists = (!empty($this->request->get['attendant_id'])) ? $this->model_pages_attendants->attendantExists($this->request->post['attendant_number'], $this->user->getId(), $this->request->get['attendant_id']) : $this->model_pages_attendants->attendantExists($this->request->post['attendant_number'], $this->user->getId());
            
            if ($attendant_exists) {
				$this->setError($this->data['text_error_attendant_already_exists']);
				return false;
			}
            
            $total = $this->model_pages_attendants->getTotalUserAttendants($this->user->getId());
            
            if ($this->secure->to_int($limitations['attendants']) <= $total && $this->secure->to_int($limitations['attendants']) != 0) {
                $this->setError($this->data['text_error_limitation_attendants']);
                return false;
            }
            
            return true;
        }
    }
    