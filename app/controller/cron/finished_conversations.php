<?php
    
    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
    
    class ControllerCronFinishedConversations extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
        
        public function index() {
            if ($this->validate()) {
                $this->load_model('pages/extensions');
                $this->load_model('pages/system');
                $this->load_model('webhook/messages');
                $this->load_model('webhook/settings');
                $this->load_model('webhook/users');
                
                $extensions_config = $this->model_pages_extensions->getConfigExtensionByCode('finished_conversations');
                
                $this->log->write('Configurações da extensão Finished Conversations: ' . print_r($extensions_config, true));
                
                $status = 'enabled';
                
                foreach($extensions_config as $config)  {
                  if ($config['config'] == 'status' && $config['value'] == 0) {
                    $this->log->write('Módulo Finished Conversations está desativado.');
                    $status = 'disabled';
                  }
                }
                
                $this->system = $this->model_pages_system->get();
                
                $conversations = $this->model_webhook_messages->getConversationsByTimeOut();
                
                $this->log->write('Conversas que o tempo de conversa foi encerrado: ' . print_r($conversations, true));
                
                foreach($conversations as $conversation) {
                    $queryMessage = $this->model_webhook_messages->getMessageByEvent($conversation['user_id'], 'timeout');
                    
                    $this->log->write('Conteúdo da variável $queryMessage: ' . print_r($queryMessage, true));
                    
                    $this->model_webhook_messages->finishConversation($conversation['id']);
                    
                    if ($status == 'disabled') {
                      continue;
                    }
                    
                    $user_info = $this->model_webhook_users->getUserById($conversation['user_id']);
                    
                    $this->log->write('Conteúdo da variável $queryMessage: ' . print_r($queryMessage, true));
                    
                    if (!$queryMessage) {
                        die();
                    }
                    
                    foreach($queryMessage as &$queryMessageRow) {
                        $this->log->write(print_r($queryMessageRow, true));
                            
                        $send_message = [];
                        $send_message['messaging_product'] = 'whatsapp';
                        $send_message['to'] = $conversation['customer_number'];
                        $send_message['type'] = 'text';
                         $send_message['text']['body'] = str_replace('[customer_name]', $conversation['customer_name'], json_decode($queryMessageRow['message']['message_content'], true)['content']);
                         
                         $this->setToken($this->model_webhook_settings->getToken($user_info['id']));
                         
                         $this->send($user_info['phone_id'], $send_message);
                    }
                }
            }
            
            if ($this->getError()) {
                $this->log->write('Cron Jobs Error: ' . print_r($this->getError(), true));
            }
        }
        
        private function send($phone, $data) {
            $this->log->write('Conteúdo para ser enviado: ' . print_r($data, true));
            
            $ch = curl_init();
        
            curl_setopt($ch, CURLOPT_URL, 'https://' . $this->secure->clear($this->system['api_domain']) . '/' . $this->secure->clear($this->system['api_version']) . '/' . $this->secure->clear($phone) . '/messages');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
          
            $headers = array();
            $headers[] = 'Authorization: Bearer ' . $this->getToken();
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                $this->log->write(curl_errno($ch));
                fclose($file);
            }
            $this->log->write(print_r($result, true));
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
    