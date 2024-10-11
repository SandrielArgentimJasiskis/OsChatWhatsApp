<?php
    
    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
    
    class ControllerCronAutoSchedules extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
        
        public function index() {
            if ($this->validate()) {
                $this->load_model('pages/extensions');
                $this->load_model('pages/system');
                $this->load_model('pages/schedules');
                $this->load_model('webhook/settings');
                $this->load_model('webhook/users');
                
                /* Obtém todas as listas que estão ativas */
                $extensions_config = $this->model_pages_extensions->getConfigExtensionByCode('auto_schedules');
                
                $this->log->write('Configurações da extensão Auto Schedules: ' . print_r($extensions_config, true));
                
                $status = 'enabled';
                
                foreach($extensions_config as $config)  {
                  if ($config['config'] == 'status' && $config['value'] == 0) {
                    $this->log->write('Módulo Auto Schedules está desativado.');
                    $status = 'disabled';
                  }
                }
                
                $this->system = $this->model_pages_system->get();
                
                $schedules = $this->model_pages_schedules->getActiveSchedules();
                
                $this->log->write('Agendamentos em processo: ' . print_r($schedules, true));
                
                if (!$schedules) {
                    die();
                }
                
                /* Atualiza todos os números que foram disparados nas últimas 24 horas e não mudaram o status da mensagem */
                $update_schedules_messages_data = [
                    'status'    => 'failed',
                    'temp'      => '24 HOUR'
                ];
                
                $this->model_pages_schedules->updateStatusByTemp($update_schedules_messages_data);
                
                foreach($schedules as $schedule) {
                    $this->setToken($this->model_webhook_settings->getToken($schedule['content']['user_id']));
                    
                    if (!$schedule['numbers']) {
                        $this->model_pages_schedules->finishSchedule($schedule['content']['id']);
                        
                        continue;
                    }
                    
                    $template_id = explode("|", json_decode($schedule['content']['params'], true)['template']['id'])[0];
                    
                    foreach($schedule['numbers'] as $number) {
                        $send_message = [];
                        $send_message['messaging_product'] = 'whatsapp';
                        $send_message['to'] = $number['customer_number'];
                        $send_message['type'] = 'template';
                        $send_message['template']['name'] = explode("|", json_decode($schedule['content']['params'], true)['template']['id'])[1];
                        $send_message['template']['language']['code'] = explode("|", json_decode($schedule['content']['params'], true)['template']['id'])[2];
                        
                        $vars = json_decode($schedule['content']['params'], true)['template']['vars'] ?? array();
                        
                        if (isset(json_decode($schedule['content']['params'], true)['template']['image_url'])) {
                            $send_message['template']['components'][0]['type'] = 'header';
                            $send_message['template']['components'][0]['parameters'][] = array(
                                    'type'  => 'IMAGE',
                                    'image' => ['link'  => json_decode($schedule['content']['params'], true)['template']['image_url']]
                                );
                        } else {
                            $this->log->write('Condição identificada como falsa.');
                        }
                        
                        if (!empty($vars)) {
                            $send_message['template']['components'][1]['type'] = 'body';
                            
                            foreach($vars as $parameter) {
                                $send_message['template']['components'][1]['parameters'][] = array(
                                        'type'      => "TEXT",
                                        'text'      => $parameter
                                    );
                            }
                        }
                        
                         $this->send($schedule['content']['number'],  $send_message, $schedule['content']['id']);
                    }
                }
            }
            
            if ($this->getError()) {
                $this->log->write('Cron Jobs Error: ' . print_r($this->getError(), true));
            }
        }
        
        private function send($phone, $data, $schedule_id) {
            $this->log->write('Conteúdo para ser enviado: ' . print_r($data, true));
            
            $ch = curl_init();
        
            curl_setopt($ch, CURLOPT_URL, 'https://' . $this->secure->clear($this->system['api_domain']) . '/' . $this->secure->clear($this->system['api_version']) . '/' . $this->secure->clear($phone) . '/messages');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
          
            $headers = array();
            $headers[] = 'Authorization: Bearer ' . $this->getToken();
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            $this->log->write('Objeto a ser enviado: ' . print_r($ch, true));
            
            $result = curl_exec($ch);
            
            if (curl_errno($ch)) {
                $this->log->write("Resultado do agendamento em massa: " . curl_errno($ch));
                
                $error_data = array(
                    'schedule_id'   => $schedule_id,
                    'number'        => $data['to'],
                    'wamid'         => '',
                    'status'        => 'failed',
                    'content'       => curl_errno($ch)
                );
                
                $this->model_pages_schedules->addStatus($error_data);
            } else {
                $this->log->write("Resultado do agendamento em massa: " . $result);
                
                if (strpos($result, 'error')) {
                    $error_data = array(
                        'schedule_id'   => $schedule_id,
                        'number'        => $data['to'],
                        'wamid'         => '',
                        'status'        => 'failed',
                        'content'       => $result
                    );
                    
                    $this->model_pages_schedules->addStatus($error_data);
                } else {
                    $this->log->write("Resultado do agendamento em massa: " . $result);
                    
                    $wamid = json_decode($result, true)['messages'][0]['id'];
                    
                    $success_data = array(
                        'schedule_id'   => $schedule_id,
                        'number'        => $data['to'],
                        'wamid'         => $wamid,
                        'status'        => 'processing',
                        'content'       => ''
                    );
                    
                    $this->model_pages_schedules->addStatus($success_data);
                }
            }
            
            curl_close($ch);
        }
        
        private function validate() {
            if (empty($this->request->get['cron_token'])) {
                $this->setError($this->data['text_error_cron_token_format']);
                return false;
            }
            
            $this->load_model('pages/system');
            
            $system = $this->model_pages_system->get();
            
            if ($this->request->get['cron_token'] != $system['cron_token']) {
                $this->setError($this->data['text_error_cron_token_format']);
                return false;
            }
            
            return true;
        }
    }
    