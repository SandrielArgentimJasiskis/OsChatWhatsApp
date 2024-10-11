<?php
    
    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
    
    class ControllerPagesSchedules extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
        
        public function index() {
            $schedules = $this->getList();
            
            if ($schedules) {
                foreach($schedules as $schedule) {
                    $this->data['schedules'][] = array(
                        'id'                => $schedule['id'],
                        'title'             => $schedule['title'],
                        'status'            => $schedule['status'],
                        'text_status'       => $this->data[sprintf('text_status_%s', $schedule['status'])],
                        'date'              => date_format(date_create($schedule['date']), 'd/m/Y h:i:s'),
                        'edit'              => $this->url->link('pages/schedules/edit', '&schedule_id=' . $schedule['id']),
                        'play'          => $this->url->link('pages/schedules/play', '&schedule_id=' . $schedule['id']),
                        'pause'             => $this->url->link('pages/schedules/pause', '&schedule_id=' . $schedule['id']),
                        'cancel'            => $schedule['id'],
                        'question_cancel'   => sprintf($this->data['text_question_cancel'], $schedule['title'])
                    );
                }
            }
            
            $this->data['add'] = $this->url->link('pages/schedules/add');
            
            if ($this->getError()) {
                $this->data['error'] = $this->error;
            }
            
            if (!empty($this->request->session['msg'])) {
                $this->data['msg'] = $this->request->session['msg'];
                
                $this->session->destroy('msg');
            }
            
            $this->data['url'] = $this->url->link('pages/schedules/cancel');
        
            $this->template->display($this->load_view('pages/schedules_list', $this->secure->remove_tags($this->data, '<i>', ['header', 'footer'])));
        }
        
        public function add() {
            if (!empty($this->request->post) && $this->validate(true)) {
                $this->request->post['file'] = $this->request->files;
                
                $this->log->write(print_r($this->request->post['file'], true));
                
                if (file_exists($this->request->post['file']['file']['tmp_name'])) {
                    move_uploaded_file($this->request->post['file']['file']['tmp_name'], DIR_MEDIA . 'upload/schedule/' . $this->request->post['file']['file']['name']);
                }
                
                $this->load_model('pages/schedules', 'add', (array)json_encode($this->request->post));
                
                $this->session->data('msg', $this->data['text_msg_success_add']);
                
                $this->url->redirect('pages/schedules/index');
            }
            
            if ($this->getError()) {
                $this->data['error'] = $this->getError();
            }
            
            $messages = $this->load_controller('pages/messages', 'getList', []);
            foreach($messages as $message) {
                if ($message['type'] == 'template') {
                    $this->data['templates'][] = array(
                        'id'        => $message['id'],
                        'title'     => $message['message_title']
                    );
                }
            }
            
            $system = $this->model_pages_system->get();
            $this->data['api_domain'] = $system['api_domain'];
            $this->data['api_version'] = $system['api_version'];
            
            $settings = $this->load_model('pages/settings', 'getSettings', (array)$this->user->getId());
            
            $this->data['app_id'] = $this->request->post['app_id'] ?? $settings['app_id'];
            $this->data['whatsapp_business_account_id'] = $this->request->post['whatsapp_business_account_id'] ?? $settings['whatsapp_business_account_id'];
            $this->data['phone_id'] = $this->request->post['phone_id'] ?? $settings['phone_id'];
            $this->data['token'] = $this->request->post['token'] ?? $settings['token'];
            
            $this->data['phone_numbers'] = $this->load_controller('pages/settings', 'getPhones', [$this->data]);
            
            $this->data['action'] = $this->url->link('pages/schedules/add');
            
            $this->data['title'] = $this->data['text_title_add'];
            $this->data['send'] = $this->data['text_button_submit_add'];
            
            $this->data['schedule_title'] = $this->request->post['title'] ?? '';
            $this->data['message_template_id'] = $this->request->post['message_template_id'] ?? array();
            $this->data['phone_number'] = $this->request->post['phone_number'] ?? '';
            $this->data['numbers'] = false;
            
			$this->data['url_get_schedule'] = $this->url->link('pages/schedules/getschedule');
        
            $this->template->display($this->load_view('pages/schedules_form', $this->secure->remove_tags($this->data, '', ['header', 'footer'])));
        }
        
        public function edit() {
            if (!empty($this->request->post) && $this->validate(false)) {
                if (empty($this->request->get['schedule_id'])) {
                    $this->url->redirect('pages/schedules/add');
                }
                
                $this->request->post['schedule_id'] = $this->request->get['schedule_id'] ?? 0;
                
                $this->request->post['file'] = $this->request->files;
                
                $this->load_model('pages/schedules', 'edit', (array)json_encode($this->request->post));
                
                $this->session->data('msg', $this->data['text_msg_success_add']);
                
                $this->url->redirect('pages/schedules/index');
            }
            
            if ($this->getError()) {
                $this->data['error'] = $this->getError();
            }
            
            $messages = $this->load_controller('pages/messages', 'getList', []);
            foreach($messages as $message) {
                if ($message['type'] == 'template') {
                    $this->data['templates'][] = array(
                        'id'        => $message['id'],
                        'title'     => $message['message_title']
                    );
                }
            }
            
            $schedule_id = $this->request->get['schedule_id'] ?? 0;
            
            $schedule_info = $this->load_model('pages/schedules', 'getSchedule', (array)$schedule_id);
            if (!$schedule_info) {
                $this->url->redirect('pages/schedules/add');
            }
            
            $system = $this->model_pages_system->get();
            $this->data['api_domain'] = $system['api_domain'];
            $this->data['api_version'] = $system['api_version'];
            
            $settings = $this->load_model('pages/settings', 'getSettings', (array)$this->user->getId());
            
            $this->data['app_id'] = $this->request->post['app_id'] ?? $settings['app_id'];
            $this->data['whatsapp_business_account_id'] = $this->request->post['whatsapp_business_account_id'] ?? $settings['whatsapp_business_account_id'];
            $this->data['phone_id'] = $this->request->post['phone_id'] ?? $schedule_info['content']['number'] ?? $settings['phone_id'];
            $this->data['token'] = $this->request->post['token'] ?? $settings['token'];
            
            $this->data['phone_numbers'] = $this->load_controller('pages/settings', 'getPhones', [$this->data]);
            
            $this->data['action'] = $this->url->link('pages/schedules/edit', '&schedule_id=' . $this->secure->to_int($schedule_id));
            
            $this->data['title'] = $this->data['text_title_edit'];
            $this->data['send'] = $this->data['text_button_submit_edit'];
            
            $this->data['schedule_title'] = $this->request->post['title'] ?? $schedule_info['content']['title'];
            $this->data['message_template_id'] = $this->request->post['message_template_id'] ?? $schedule_info['content']['message_template_id'];
            $this->data['phone_number'] = $this->request->post['phone_number'] ?? $schedule_info['content']['number'];
            $this->data['numbers'] = $schedule_info['numbers'] ?? false;
            $this->data['date'] = $schedule_info['content']['date'];
            
            if ($this->data['numbers']) {
                $this->data['download'] = $this->url->link('pages/schedules/download', '&schedule_id=' . $this->secure->to_int($schedule_id));
            }
            
            $this->data['schedule_content'] = $this->request->post['schedule_content'] ?? $schedule_info['content'];
            
			$this->data['url_get_schedule'] = $this->url->link('pages/schedules/getschedule');
            
            $this->template->display($this->load_view('pages/schedules_form', $this->secure->remove_tags($this->data, '', ['header', 'footer'])));
        }
        
        public function cancel() {
            if (empty($this->request->post['schedule_id'])) {
                $this->setError($this->data['text_error_schedule_id']);
            }
            
            if (!empty($this->request->post['schedule_id'])) {
                $this->load_model('pages/schedules');
                
                $schedule_status = $this->model_pages_schedules->getScheduleStatus($this->request->post['schedule_id']);
                
                if (!$schedule_status) {
                    $this->setError($this->data['text_error_schedule_id']);
                }
                if (in_array($schedule_status, ['finished', 'cancelled'])) {
                    $this->setError($this->data['text_error_schedule_status']);
                }
            }
            
            if (!$this->getError()) {
                $this->session->data('msg', $this->data['text_msg_success_cancel']);
                    
                $this->model_pages_schedules->cancel($this->request->post['schedule_id']);
                
                $this->url->redirect('pages/schedules/index');
            }
            
            $this->index();
        }
        
        public function play() {
            if (empty($this->request->get['schedule_id'])) {
                $this->setError($this->data['text_error_schedule_id']);
            }
            
            if (!empty($this->request->get['schedule_id'])) {
                $this->load_model('pages/schedules');
                
                $schedule_status = $this->model_pages_schedules->getScheduleStatus($this->request->get['schedule_id']);
                
                if (!$schedule_status) {
                    $this->setError($this->data['text_error_schedule_id']);
                }
                if (in_array($schedule_status, ['finished', 'cancelled'])) {
                    $this->setError($this->data['text_error_schedule_status']);
                }
            }
            
            if (!$this->getError()) {
                $this->session->data('msg', $this->data['text_msg_success_play']);
                    
                $this->model_pages_schedules->play($this->request->get['schedule_id']);
                
                $this->url->redirect('pages/schedules/index');
            }
            
            $this->index();
        }
        
        public function pause() {
            if (empty($this->request->get['schedule_id'])) {
                $this->setError($this->data['text_error_schedule_id']);
            }
            
            if (!empty($this->request->get['schedule_id'])) {
                $this->load_model('pages/schedules');
                
                $schedule_status = $this->model_pages_schedules->getScheduleStatus($this->request->get['schedule_id']);
                
                if (!$schedule_status) {
                    $this->setError($this->data['text_error_schedule_id']);
                }
                if (in_array($schedule_status, ['finished', 'cancelled'])) {
                    $this->setError($this->data['text_error_schedule_status']);
                }
            }
            
            if (!$this->getError()) {
                $this->session->data('msg', $this->data['text_msg_success_pause']);
                    
                $this->model_pages_schedules->pause($this->request->get['schedule_id']);
                
                $this->url->redirect('pages/schedules/index');
            }
            
            $this->index();
        }
        
        public function readFile() {
            $this->log->write('Conteúdo da Request: ' . print_r($this->request, true));
            
            $this->log->write($this->request->post_json, true);
        }
        
        public function getSchedule() {
            $schedule = $this->load_model('pages/schedules', 'getschedule', (array)$this->request->get['schedule_id']);
            
            $this->data['schedule'] = $schedule;
            
            $this->template->display($this->load_view('pages/schedule_json', $this->secure->remove_tags($this->data)));
        }
        
        public function getList() {
            return $this->load_model('pages/schedules', 'getUserSchedules', (array)$this->user->getId());
        }
        
        public function download() {
            $schedule_id = $this->request->get['schedule_id'] ?? 0;
            
            $schedule_info = $this->load_model('pages/schedules', 'getSchedule', (array)$schedule_id);
            
            if ($schedule_info) {
                $file = $schedule_info['content']['file'];
                
                if (file_exists(DIR_MEDIA . 'upload/schedule/' . $file)) {
                    // Cabeçalhos HTTP
                    header('Content-Description: File Transfer');
                    header('Content-Type: text/plain; charset=utf-8');
                    header('Content-Disposition: attachment; filename="' . basename(DIR_MEDIA . 'upload/schedule/' . $file) . '"');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize(DIR_MEDIA . 'upload/schedule/' . $file));
                
                    // Envia o arquivo para o navegador
                    readfile(DIR_MEDIA . 'upload/schedule/' . $file);
                    die();
                }
            }
        }
        
        private function validate($file_required) {
            if (!$this->secure->is_schedule_title($this->request->post['title'])) {
                $this->setError($this->data['text_error_schedule_title_format']);
                return false;
            }
            
            if ($file_required) {
                if (!$this->secure->is_txt_file($this->request->files['file']['tmp_name'])) {
                    $this->setError($this->data['text_error_txt_file_format']);
                    return false;
                }
            }
            
            return true;
        }
    }
    