<?php
    
    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
    
    class ControllerWebhookWhatsApp extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
        
        public function __construct($object) {
            parent::__construct($object);
            
            $this->load_model('pages/system');
            $this->load_model('webhook/attendants');
            $this->load_model('webhook/messages');
            $this->load_model('webhook/settings');
            $this->load_model('webhook/users');
            
            $this->system = $this->model_pages_system->get();
            
            if (!empty($this->request->get['hub_challenge'])) {
                $data['token'] = $this->request->get['hub_challenge'];
                
                $this->template->display($this->load_view('webhook/whatsapp', $this->secure->remove_tags($data)));
            }
                
            $this->post = json_decode($this->request->post_json);
            
            if (empty($this->post->entry)) { 
                die();
            }
            
            $this->post = json_decode($this->request->post_json);
        }
        
        public function index() {
            $data = array();
            
            $translations = $this->load_language('webhook/whatsapp');
            
            $this->post = $this->post->entry[0]->changes[0]->value;
            
            $this->log->write(print_r($this->post, true));
            
             if (!empty($this->post->statuses)) {
                $messages_statuses = $this->post->statuses;
                foreach($messages_statuses as $message_status) {
                    $status = $message_status->status;
                    $phone = $message_status->recipient_id;
                    
                    /* Atualiza o status da mensagem de agendamento em massa */
                    $data = [
                        'number'    => $phone,
                        'wamid'     => $message_status->id
                    ];
                    
                    $this->log->write("Dados para atualizar o status de envio do agendamento em massa: " . print_r($data, true));
                    
                    $this->model_webhook_messages->updateWamidStatus($data);
                    
                    /* Adiciona a mensagem ao histórico do chat */
                    $this->model_webhook_messages->addStatus($status, $phone);
                }
                die();
            }
            
            if (empty($this->post->messages)) {
                die();
            }
            
            $this->log->write('Conteúdo do $this->post: ' . print_r($this->post, true));
            $messages = $this->post->messages;
            
            // Obtém o usuário pelo número de telefone.
            $user = $this->model_webhook_users->getUserByPhone($this->post->metadata->phone_number_id);
            
            $this->setToken($this->model_webhook_settings->getToken($user['user_id']));
            
            // Obtém o nome que estiver configurado no número de telefone.
            $customer_name = $this->post->contacts[0]->profile->name;
            
            $customer_menu = 0;
            
            // Percorre a lista de mensagens recebidas.
            foreach($messages as &$message) {
                $this->log->write('Percorrendo as mensagens recebidas: ' . print_r($message, true));
                if (is_array($message)) {
                    if (!empty($message[0])) {
                        $queue_id = $message['queue_id'];
                        $message = $message[0];
                        
                        $message->queue_id = $queue_id;
                        unset($queue_id);
                    }
                }
                
                // Verifica o tamanho da mensagem do tipo texto.
                if ($message->type == 'text') {
                    if (strlen($message->text->body) > 2056) {
                        $send_message = [];
                        $send_message['messaging_product'] = 'whatsapp';
                        $send_message['to'] = $message->from;
                        $send_message['type'] = 'text';
                        $send_message['text']['body'] = $translations['text_error_message_long'];
                        
                        $this->send($send_message);
                        continue;
                    }
                }
                
                //  Verifica se o remetente é um atendente, a quantidade de conversas iniciadas pelo mesmo e se não é um botão interativo.
                if ((empty($message->interactive->button_reply->id)) && (empty($message->interactive->list_reply->id))) {
                    $is_attendant = $this->model_webhook_attendants->is_attendant_number($user['user_id'], $message->from);
                    
                    $this->log->write('É um atendente: ' . print_r($is_attendant, true));
                    
                    if ($is_attendant) {
                        $attendant_info = array(
                            'number'    => $is_attendant['number'],
                            'name'      => $is_attendant['name']
                        );
                        
                        $conversations_attendant_count = $this->model_webhook_messages->getConversationAttendantCount($user['user_id'], $attendant_info);
                        
                        $this->log->write('Total de conversas iniciadas/aceitas pelo atendente: ' . print_r($conversations_attendant_count, true));
                        
                        if ($conversations_attendant_count == 0) {
                            $this->log->write('Nenhuma conversa foi iniciada/aceita pelo atendente!');
                            
                            $send_message = [];
                            $send_message['messaging_product'] = 'whatsapp';
                            $send_message['to'] = $message->from;
                            $send_message['type'] = 'text';
                            $send_message['text']['body'] = $translations['text_error_attendant_init_conversation'];
                            
                            $this->send($send_message);
                            die();
                        }
                        
                        if (($conversations_attendant_count >= 2) && (empty($message->interactive->list_reply->id))) {
                            $this->log->write('Identificadas mais de uma conversa iniciadas/aceitas pelo atendente.');
                            $next_send = $this->model_webhook_messages->getNextSend($user['user_id']);
                            
                            $this->log->write('Próximo número que receberá as mensagens: ' . $next_send);
                            
                            if (!$next_send) {
                                $customer_menu = $this->get_customer_menu($user['user_id'], $attendant_info, $translations);
                                        
                                $this->log->write('Conteúdo do menu que será disparado: ' . print_r($customer_menu, true));
                                
                                $this->send($customer_menu);
                                die();
                            } else {
                                $conversations_customer_count = $this->model_webhook_messages->getConversationCountByCustomerNumber($user['user_id'],  $next_send);
                                
                                if ($conversations_customer_count == 0) {
                                    $customer_menu = $this->get_customer_menu($user['user_id'], $attendant_info, $translations);
                                        
                                    $this->log->write('Conteúdo do menu que será disparado: ' . print_r($customer_menu, true));
                                    
                                    $this->send($customer_menu);
                                    die();
                                }
                            }
                        }
                    }
                }
                
                // Obtém a conversa do usuário.
                $conversation = $this->model_webhook_messages->getConversation($user['user_id'], $message->from);
                
                $this->log->write('Conteúdo da conversa localizada e buscada: ' . print_r($conversation, true));
                
                if (!empty($message->interactive->list_reply->id)) {
                    $this->log->write('Primeiros 11 caracteres do botão iniciar conversa com cliente: ' . substr($message->interactive->list_reply->id, 0, 11));
                    if (substr($message->interactive->list_reply->id, 0, 11) == 'contact_to_') {
                        
                        $this->log->write('Identificada solicitação para conversar com o cliente.');
                        
                        $number = $this->model_webhook_messages->getConversationCountByCustomerNumber($user['id'], substr($message->interactive->list_reply->id, 11));
                        
                        if ($number == 1) {
                            $send_message = [];
                            $send_message['messaging_product'] = 'whatsapp';
                            $send_message['to'] = $message->interactive->list_reply->id;
                            $send_message['type'] = 'text';
                            $send_message['text']['body'] = $translations['text_thank_you_for_wait'];
                            
                            $this->send($send_message);
                            
                            $this->model_webhook_messages->addNextSend($user['id'], substr($message->interactive->list_reply->id, 11));
                            
                            $queue = $this->model_webhook_messages->getConversationQueueByCustomerNumber(substr($message->interactive->list_reply->id, 11));
                            $this->log->write('Conteúdo da lista de espera: ' . print_r($queue, true));
                            
                            $messages = $queue;
                            
                            continue;
                        } else {
                            $send_message = [];
                            $send_message['messaging_product'] = 'whatsapp';
                            $send_message['to'] = $message->from;
                            $send_message['type'] = 'text';
                            $send_message['text']['body'] = $translations['text_error_conversation_init'];
                            
                            $this->send($send_message);
                            die();
                        }
                    }
                }
                
                if (!empty($conversation['customer_menu'])) {
                    $attendant_info = array(
                        'number'    => $conversation['attendant_number'],
                        'name'      => $conversation['attendant_name']
                    );
                    
                    $customer_menu = $this->get_customer_menu($user['user_id'], $attendant_info, $translations);
                            
                    $this->log->write('Conteúdo do menu que será disparado: ' . print_r($customer_menu, true));
                    
                    $this->send($customer_menu);
                    die();
                }
                
                if (!empty($message->interactive->button_reply->id)) {
                    $this->log->write('Identificado envio de clique em botão. ' . print_r($message->interactive->button_reply->id, true));
                    
                    if (substr($message->interactive->button_reply->id, 0, 5) == 'init_') {
                        $conversation = $this->model_webhook_messages->getConversationById(substr($message->interactive->button_reply->id, 5));
                        
                        $this->log->write('Conteúdo da conversa buscada pelo botão iniciar conversa: ' . print_r($conversation, true));
                        
                        if (!$conversation) {
                            $send_message = [];
                            $send_message['messaging_product'] = 'whatsapp';
                            $send_message['to'] = $message->from;
                            $send_message['type'] = 'text';
                            $send_message['text']['body'] = $translations['text_error_conversation_init'];
                            
                            $this->send($send_message);
                            die();
                        }
                        
                        $this->model_webhook_messages->initConversation(substr($message->interactive->button_reply->id, 5));
                        
                        $this->model_webhook_messages->addNextSend($user['id'], substr($message->interactive->button_reply->id, 5));
                        
                        // Envia a mensagem com botão para finalizar a conversa com o cliente.
                        $send_message = array();
                        
                        $send_message['messaging_product'] = 'whatsapp';
                        $send_message['to'] = $conversation['attendant_number'];
                        $send_message['type'] = 'interactive';
                        
                        $send_message['interactive']['type'] = 'button';
                        
                        $send_message['interactive']['body']['text'] = $translations['text_message_sendded_by_customer'] . $translations['text_message_text_finish'];
                            
                        $send_message['interactive']['body']['text'] = str_replace('%s', $customer_name, $send_message['interactive']['body']['text']);
                        
                        $button_content = array(
                            'id'    => 'finish_' . $conversation['id'],
                            'title' => $translations['text_message_button_finish']
                        );
                        $send_message['interactive']['action']['buttons'][0] = array(
                            'type'  => 'reply',
                            'reply' => $button_content
                        );
                        
                        $this->send($send_message);
                        
                        // Envia a mensagem informando o cliente que o atendente iniciou a conversa.
                        $conversation_query = $this->model_webhook_messages->getConversationById(substr($message->interactive->button_reply->id, 7));
                        
                        $this->log->write('Conteúdo da conversa: ' . print_r($conversation_query, true));
                        
                        $queryMessage = $this->model_webhook_messages->getMessageByEvent($user['id'], 'started_by_attendant');
                        
                        foreach($queryMessage as &$queryMessageRow) {
                            $this->log->write(print_r($queryMessageRow, true));
                            
                            $send_message = [];
                            $send_message['messaging_product'] = 'whatsapp';
                            $send_message['to'] = $conversation['customer_number'];
                            $send_message['type'] = 'text';
                            $send_message['text']['body'] = json_decode($queryMessageRow['message']['message_content'], true)['content'];
                            
                            // Substitui o nome da variável pelo seu conteúdo.
                            $send_message['text']['body'] = str_replace('[customer_name]', $conversation['customer_name'], $send_message['text']['body']);
                            $send_message['text']['body'] = str_replace('[attendant_name]', $conversation['attendant_name'], $send_message['text']['body']);
                            
                            // Limita o número de caracteres.
                            $send_message['text']['body'] = substr($send_message['text']['body'], 0, 4095);
                             
                             $this->send($send_message);
                        }
                        
                        $queryMessage = null;
                        
                        $attendant_info = array(
                            'number'    => $conversation['attendant_number'],
                            'name'      => $conversation['attendant_name']
                        );
                        
                        $this->log->write('Conteúdo do atendente: ' . print_r($attendant_info, true));
                        
                        $conversation_quantity = $this->model_webhook_messages->getConversationAttendantCount($user['user_id'], $attendant_info);
                        
                        $this->log->write('Quantidade de conversas aceitas pelo atendente: ' . print_r($conversation_quantity, true));
                        if ($conversation_quantity > 1) {
                            $customer_menu = $this->get_customer_menu($user['id'], $attendant_info, $translations);
                            
                            $this->log->write('Conteúdo do menu que será disparado: ' . print_r($customer_menu, true));
                            
                            $this->send($customer_menu);
                        }
                        
                        // Envia tudo o que estiver na lista de espera.
                        $queue = $this->model_webhook_messages->getConversationQueueByCustomerNumber($conversation['customer_number']);
                            $this->log->write('Conteúdo da lista de espera: ' . print_r($queue, true));
                            
                        $messages = $queue;
                            
                        continue;
                    }
                    
                    if (substr($message->interactive->button_reply->id, 0, 7) == 'finish_') {
                        $conversation_query = $this->model_webhook_messages->getConversationById(substr($message->interactive->button_reply->id, 7));
                        
                        if (!$conversation_query) {
                            $send_message = [];
                            $send_message['messaging_product'] = 'whatsapp';
                            $send_message['to'] = $message->from;
                            $send_message['type'] = 'text';
                            $send_message['text']['body'] = $translations['text_error_conversation_finish'];
                            
                            $this->send($send_message);
                            die();
                        }
                        
                        $this->model_webhook_messages->finishConversation(substr($message->interactive->button_reply->id, 7));
                        
                        // Envia a mensagem informando o cliente que o atendente encerrou a conversa.
                        $queryMessage = $this->model_webhook_messages->getMessageByEvent($user['id'], 'finished_by_attendant');
                        
                        foreach($queryMessage as &$queryMessageRow) {
                            $this->log->write(print_r($queryMessageRow, true));
                            
                            $send_message = [];
                            $send_message['messaging_product'] = 'whatsapp';
                            $send_message['to'] = $conversation_query['customer_number'];
                            $send_message['type'] = 'text';
                            $send_message['text']['body'] = json_decode($queryMessageRow['message']['message_content'], true)['content'];
                            
                            // Substitui o nome da variável pelo seu conteúdo.
                            $send_message['text']['body'] = str_replace('[customer_name]', $conversation_query['customer_name'], $send_message['text']['body']);
                            $send_message['text']['body'] = str_replace('[attendant_name]', $conversation_query['attendant_name'], $send_message['text']['body']);
                            
                            // Limita o número de caracteres.
                            $send_message['text']['body'] = substr($send_message['text']['body'], 0, 4095);
                             
                             $this->log->write('Conteúdo do disparo clique no botão finalizar conversa: ' . print_r($send_message, true));
                             
                             $this->send($send_message);
                        }
                        
                        $attendant_info = array(
                            'number'    => $conversation_query['attendant_number'],
                            'name'      => $conversation_query['attendant_name']
                        );
                        
                        $this->log->write('Conteúdo do atendente: ' . print_r($attendant_info, true));
                        
                        $conversation_quantity = $this->model_webhook_messages->getConversationAttendantCount($user['user_id'], $attendant_info);
                        
                        $this->log->write('Quantidade de conversas aceitas pelo atendente: ' . print_r($conversation_quantity, true));
                        if ($conversation_quantity > 1) {
                            $customer_menu = $this->get_customer_menu($user['id'], $attendant_info, $translations);
                            
                            $this->log->write('Conteúdo do menu que será disparado: ' . print_r($customer_menu, true));
                            
                            $this->send($customer_menu);
                        }
                        
                        continue;
                    }
                }
                
                // Define as informações do Atendente.
                $attendant_info = array(
                    'name'  => $conversation['attendant_name'] ?? '',
                    'number' => $conversation['attendant_number'] ?? ''
                );
                
                // Define o tipo da última mensagem enviada.
                $last_message_type = $conversation['last_message_type'] ?? 'text';
                
                // Define as informações do status da conversa.
                $status = $conversation['status'] ?? '';
                
                $this->log->write('RESULTADO DA BUSCA DE UMA CONVERSA JÁ INICIADA: ' . print_r($conversation, true));
                
                if ($conversation) {
                    if (($conversation['customer_number'] == $message->from) && ($conversation['status'] == 'started')) {
                        if (!empty($message->interactive->list_reply->id)) {
                            $send_message = [];
                            $send_message['messaging_product'] = 'whatsapp';
                            $send_message['to'] = $message->from;
                            $send_message['type'] = 'text';
                            $send_message['text']['body'] = $translations['text_error_conversation_started'];
                            
                            $this->send($send_message);
                            die();
                        }
                    }
                }
                
                 // Obtém as mensagens que serão enviadas pelo evento.
                if (!$conversation) {
                    // Define o conteúdo da conversa como a mensagem enviada pelo remetente.
                    $content = json_encode([
                        'type'          => 'text',
                        'text_content'  => $message->text->body
                    ]);
                    
                    $last_message_type = 'text';
                    
                    $last_message_id = $queryMessageRow['message']['id'];
                    
                    $attendant_info = [
                        'number'    => '',
                        'name'      => ''
                    ];
                    
                    $this->model_webhook_messages->addHistory($conversation['id'], $user['user_id'], $last_message_id, $content ?? '', $message->from, $attendant_info);
                    
                    
                    // Busca a mensagem inicial pelo id do usuário.
                    $queryMessage = $this->model_webhook_messages->getMessageByEvent($user['id'], 'init');
                    
                    // Obtém as informações do Atendante
                    $attendant_info = $this->model_webhook_attendants->getAttendant(0);
                }
                
                if ($conversation) {
                    if ($conversation['attendant_name'] == '') {
                        if ($message->type == "text") {
                            if ($this->model_webhook_messages->messageKeywordExists($message->text->body)) {
                                $content = json_encode([
                                    'type'              => 'keyword',
                                    'keyword_content'   => $message->text->body
                                ]);
                                
                                $last_message_id = $queryMessageRow['message']['id'] ?? 0;
                                
                                $attendant_info = [
                                    'number'    => '',
                                    'name'      => ''
                                ];
                                
                                $this->model_webhook_messages->addHistory($conversation['id'], $user['user_id'], $last_message_id, $content ?? '', $message->from, $attendant_info);
                            }
                            $queryMessage = $this->model_webhook_messages->getMessageByEvent($user['id'], 'response', $message->text->body, 'text');
                        } elseif ($message->type == "interactive") {
                            $message_option_content = $this->model_webhook_messages->getMessageOption($message->interactive->list_reply->id);
                            
                            /* Adiciona ao histórico que uma opção do menu foi selecionada */
                            $content = json_encode([
                                'message_option_id'         => $message->interactive->list_reply->id,
                                'message_option_content'    => $message_option_content
                            ]);
                            
                            $last_message_id = $queryMessageRow['message']['id'];
                            
                            $attendant_info = [
                                'number'    => '',
                                'name'      => ''
                            ];
                            
                            $this->model_webhook_messages->addHistory($conversation['id'], $user['user_id'], $last_message_id, $content ?? '', $message->from, $attendant_info);
                            $queryMessage = $this->model_webhook_messages->getMessageByEvent($user['id'], 'response', $message->interactive->list_reply->id, 'option');
                        }
                    } else {
                        // Adicionará as menssagens do cliente na fila de espera se o mesmo não for o próximo a ser atendido.
                        $attendant_info = array(
                            'number'    => $conversation['attendant_number'],
                            'name'      => $conversation['attendant_name']
                        );
                        
                        $conversation_quantity = $this->model_webhook_messages->getConversationAttendantCount($user['user_id'], $attendant_info);
                        if ($conversation_quantity > 1) {
                            $next_send = $this->model_webhook_messages->getNextSend($user['user_id']);
                            
                            if ($next_send) {
                                if ((($next_send != $message->from) && $next_send != $conversation['attendant_number']) && $next_send != $conversation['customer_number']) {
                                    $data = array(
                                        'conversation_id'   => $conversation['id'],
                                        'user_id'           => $user['user_id'],
                                        'customer_number'   => $conversation['customer_number'],
                                        'customer_name'     => $conversation['customer_name'],
                                        'attendant_number'  => $conversation['attendant_number'],
                                        'attendant_name'    => $conversation['attendant_name'],
                                        'message'           => json_encode($messages)
                                    );
                                    
                                    $this->model_webhook_messages->addConversationQueue($data);
                                    
                                    $send_message = [];
                                    $send_message['messaging_product'] = 'whatsapp';
                                    
                                    $send_message['to'] = $conversation['customer_number'];
                                    
                                    $send_message['type'] = 'text';
                                    $send_message['text']['body'] = $translations['text_message_please_wait'];
                                    
                                    $this->send($send_message);
                                    
                                    die();
                                }
                            }
                        }
                    }
                }
                
                $this->log->write('Conteúdo das mensagens buscadas pelo evento: ' . print_r($queryMessage, true));
                
                $saved_conversation = [];
                
                if (empty($queryMessage)) {
                    $queryMessage[0] = array(
                        
                    );
                }
                
                foreach($queryMessage as &$queryMessageRow) {
                    $this->log->write('Conteúdo da variável "$queryMessageRow": ' . print_r($queryMessageRow, true));
                    
                    $send_message = [];
                    $send_message['messaging_product'] = 'whatsapp';
                    
                    // Enviará a mensagem para o remetente caso não exista a conversa.
                    if (!$conversation) {
                        $send_message['to'] = $message->from;
                    }
                    
                    // Se houver conversa busca o número do atendente.
                    $to = [];
                    if ($conversation) {
                        if (!empty($queryMessageRow['message']['attendant_id'])) {
                            $this->log->write('$queryMessageRow[\'message\'][\'attendant_id\'] encontrado com o id: ' . $queryMessageRow['message']['attendant_id']);
                            $attendant_info = $this->model_webhook_attendants->getAttendant($queryMessageRow['message']['attendant_id']);
                            $to = $attendant_info;
                            
                            $send_message['to'] = $attendant_info['number'];
                            
                            // Envia a mensagem com botão para iniciar a conversa com o cliente.
                            $next_send_message = array();
                            
                            $next_send_message['messaging_product'] = 'whatsapp';
                            $next_send_message['to'] = $send_message['to'];
                            $next_send_message['type'] = 'interactive';
                            
                            $next_send_message['interactive']['type'] = 'button';
                            
                            $next_send_message['interactive']['body']['text'] = $translations['text_message_sendded_by_customer'] . $translations['text_message_text_init'];
                                
                            $next_send_message['interactive']['body']['text'] = str_replace('%s', $customer_name, $next_send_message['interactive']['body']['text']);
                            
                            $button_content = array(
                                'id'    => 'init_' . $conversation['id'],
                                'title' => $translations['text_message_button_init']
                            );
                            $next_send_message['interactive']['action']['buttons'][0] = array(
                                'type'  => 'reply',
                                'reply' => $button_content
                            );
                            
                            $this->model_webhook_messages->ConversationChangeStatus($conversation['id'], 'waiting');
                        } else {
                            $this->log->write('$queryMessageRow[\'message\'][\'attendant_id\'] não encontrado!');
                            $attendant_info = $this->model_webhook_attendants->getAttendant(0);
                            $to = $attendant_info;
                            
                            // Enviará a mensagem para o remetente caso não exista o atendente na conversa.
                            $send_message['to'] = $message->from;
                        }
                    }
                    
                    $this->log->write('Conteúdo do atendente buscado pelo id: ' . print_r($to, true));
                    
                    if ((!empty($to['name'])) && !empty($to['number'])) {
                        if (($to['name'] != '') && $to['number']) {
                            $this->model_webhook_messages->addAttendantToConversation($conversation['id'], $to);
                        }
                    }
    
                    $query_message_type = $this->model_webhook_messages->getMessageType($conversation['message_id']);
                    
                    if ($queryMessageRow['message'] == '' || !$queryMessageRow['message']) {
                        $query_message_type = $this->model_webhook_messages->getMessageType($conversation['message_id']);
                        
                        if ($query_message_type == 'interactive') {
                            $send_message['type'] = 'text';
                            $send_message['text']['body'] = $translations['text_message_select_an_option'];
                        } else {
                            if ($conversation['attendant_name'] == '') {
                                $send_message['type'] = 'text';
                                $send_message['text']['body'] = $translations['text_message_didnt_understand'];
                            } else {
                                if (empty($queryMessageRow['customer_menu'])) {
                                    if ($conversation['status'] == 'started') {
                                        $send_message['to'] = ($conversation['attendant_number'] != $message->from) ? $conversation['attendant_number'] : $conversation['customer_number'];
                                    } else {
                                        $send_message['to'] = ($conversation['customer_number'] == $message->from) ? $conversation['customer_number'] : $conversation['attendant_number'];
                                    }
                                } else {
                                    $send_message['to'] = $conversation['attendant_number'];
                                }
                                
                                if ($conversation['status'] == 'started') {
                                    $send_message['type'] = $message->type;
                                } else {
                                    $send_message['type'] = 'text';
                                    
                                    $send_message['text']['body'] = ($conversation['customer_number'] == $message->from) ? $translations['text_message_please_wait'] : $translations['text_message_please_select_a_customer'];
                                    
                                    // Adiciona o que for enviado pelo cliente para a lista de espera.
                                    if ($conversation['customer_number'] == $message->from) {
                                        $data = array();
                                        
                                        $data = array(
                                            'conversation_id'   => $conversation['id'],
                                            'user_id'           => $user['user_id'],
                                            'customer_number'   => $conversation['customer_number'],
                                            'customer_name'     => $conversation['customer_name'],
                                            'attendant_number'  => $conversation['attendant_number'],
                                            'attendant_name'    => $conversation['attendant_name'],
                                            'message'           => json_encode($messages)
                                        );
                                        
                                        $this->log->write('Os números de quem enviou e o cliente são o mesmo. Mensagens para serem salvas na lista de espera: ' . print_r($data, true));
                                        
                                        $this->model_webhook_messages->addConversationQueue($data);
                                    } else {
                                        // Enviará o menu de clientes para o atendente.
                                        
                                        $attendant_info = array(
                                            'number'    => $conversation['attendant_number'],
                                            'name'      => $conversation['attendant_name']
                                        );
                                        
                                        $customer_menu = $this->get_customer_menu($user['user_id'], $attendant_info, $translations);
                                                
                                        $this->log->write('Conteúdo do menu que será disparado: ' . print_r($customer_menu, true));
                                        
                                        $this->send($customer_menu);
                                        die();
                                    }
                                }
                                
                                if ($conversation['status'] == 'started') {
                                    if ($message->type == "text") {
                                        $send_message[$send_message['type']]['body'] = $message->text->body;
                                    } elseif (in_array($message->type, ['image', 'video', 'audio', 'sticker'])) {
                                        $message_type = $message->type;
                                        
                                        if (!empty($message->$message_type->caption)) { $send_message[$send_message['type']]['caption'] = $message->$message_type->caption;
                                        }
                                        $send_message[$send_message['type']]['id'] = $message->$message_type->id;
                                    } elseif ($message->type == "document") { $send_message[$send_message['type']]['filename'] = $message->document->filename;
                                            $send_message[$send_message['type']]['id'] = $message->document->id;
                                        
                                        if (!empty($message->document->caption)) { $send_message[$send_message['type']]['caption'] = $message->document->caption;
                                        }
                                    } elseif ($message->type == "contacts") {
                                        $send_message[$send_message['type']] = (array)$message->contacts;
                                    } elseif ($message->type == "location") { 
                                        $send_message[$send_message['type']] = $message->location;
                                    }
                                }
                            }
                        }
                        
                        $last_message_id = 0;
                    } else {
                        $query_message_type = $this->model_webhook_messages->getMessageType($conversation['message_id']);
                        
                        if ($queryMessageRow['message']['finish'] == '1') {
                            $send_message['to'] = $conversation['customer_number'];
                            $send_message['type'] = 'text';
                            $send_message['text']['body'] = json_decode($queryMessageRow['message']['message_content'], true)['content'];
                            
                            // Substitui o nome da variável pelo seu conteúdo.
                            $send_message['text']['body'] = str_replace('[customer_name]', $conversation['customer_name'], $send_message['text']['body']);
                            $send_message['text']['body'] = str_replace('[attendant_name]', $conversation['attendant_name'], $send_message['text']['body']);
                                    
                            // Limita o número de caracteres.
                            $send_message['text']['body'] = substr($send_message['text']['body'], 0, 4095);
                         
                            $this->log->write('Conteúdo do disparo conversa finalizada pelo cliente: ' . print_r($send_message, true));
                                     
                            $this->send($send_message);
                        
                            //  Finaliza a conversa (Cliente).
                            $this->model_webhook_messages->finishConversation($conversation['id']);
                            
                            $queryMessage2 = $this->model_webhook_messages->getMessageByEvent($user['id'], 'finished_by_customer');
                        
                            if ($queryMessage2) {
                                foreach($queryMessage2 as &$queryMessageRow2) {
                                    $this->log->write(print_r($queryMessageRow2, true));
                                    
                                    $send_message = array();
                                    $send_message['messaging_product'] = 'whatsapp';
                                    $send_message['to'] = $conversation['customer_number'];
                                    $send_message['type'] = 'text';
                                    $send_message['text']['body'] = json_decode($queryMessageRow2['message']['message_content'], true)['content'];
                                    
                                    // Substitui o nome da variável pelo seu conteúdo.
                                    $send_message['text']['body'] = str_replace('[customer_name]', $conversation['customer_name'], $send_message['text']['body']);
                                    $send_message['text']['body'] = str_replace('[attendant_name]', $conversation['attendant_name'], $send_message['text']['body']);
                                    
                                    // Limita o número de caracteres.
                                    $send_message['text']['body'] = substr($send_message['text']['body'], 0, 4095);
                                     
                                     $this->log->write('Conteúdo do disparo conversa finalizada pelo cliente: ' . print_r($send_message, true));
                                     
                                     $this->send($send_message);
                                }
                                
                                continue;
                            }
                        }
                        
                        if ($queryMessageRow['message']['type'] == 'text') {
                            $send_message['type'] = $queryMessageRow['message']['type'];
                            
                            $send_message[$send_message['type']]['body'] = json_decode($queryMessageRow['message']['message_content'], true)['content'];
                            
                            /* Adiciona ao histórico que a mensagem do tipo texto foi enviada */
                            $content = json_encode([
                                'text'  => $send_message[$send_message['type']]['body']
                            ]);
                            
                            $last_message_id = $queryMessageRow['message']['id'];
                            
                            $attendant_info = [
                                'number'    => '',
                                'name'      => ''
                            ];
                            
                            $this->model_webhook_messages->addHistory($conversation['id'], $user['user_id'], $last_message_id, $content ?? '', $message->from, $attendant_info);
                        } elseif ($queryMessageRow['message']['type'] == 'interactive') {
                            $send_message['type'] = $queryMessageRow['message']['type'];
                            
                            $send_message[$send_message['type']]['type'] = 'list';
                            $send_message[$send_message['type']]['body']['text'] = json_decode($queryMessageRow['message']['message_content'], true)['content'];
                            
                            $options = array();
                            if (!empty($queryMessageRow['message']['options'])) {
                                foreach($queryMessageRow['message']['options'] as $option) {
                                    $actions[] = array(
                                            'id'    => $option['id'],
                                            'title' => $option['option_title'],
                                            'description'   => $option['option_description']
                                    );
                                }
                                
                                $sections[] = array(
                                    'title'     => 'Menu',
                                    'rows'      => $actions
                                );
                                
                                $this->log->write('Conteúdo da variável $sections: ' . print_r($sections, true));
                                
                                $send_message[$send_message['type']]['action'] = [
                                    'button'    => "Menu",
                                    'sections'  => $sections
                                ];
                                
                                $this->log->write('Conteúdo da variável $send_message[\'interactive\'][\'action\']' . print_r($send_message[$send_message['type']]['action'], true));
                                
                                /* Adiciona ao histórico que a mensagem do tipo menu foi enviada */
                                $content = json_encode([
                                    'type'  => 'interactive',
                                    'menu_content' => [
                                        'type'          => $send_message['type'],
                                        'text'          => $send_message[$send_message['type']]['body']['text']
                                    ]
                                ]);
                                
                                $last_message_id = $queryMessageRow['message']['id'];
                                
                                $attendant_info = [
                                    'number'    => '',
                                    'name'      => ''
                                ];
                                
                                $this->model_webhook_messages->addHistory($conversation['id'], $user['user_id'], $last_message_id, $content ?? '', $message->from, $attendant_info['number'], $attendant);
                            }
                        } else if ($queryMessageRow['message']['type'] == 'media') {
                            $send_message['type'] = json_decode($queryMessageRow['message']['message_content'], true)['type'];
                            
                            $send_message[$send_message['type']]['link'] = json_decode($queryMessageRow['message']['message_content'], true)['url'];
                            
                            if ($send_message['type'] == 'document') {
                                $send_message[$send_message['type']]['filename'] = 'document';
                            }
                            
                            /* Adiciona ao histórico que uma mensagem do tipo mídia foi enviada */
                            $content = json_encode([
                                'type'  => 'media',
                                'media_content' => [
                                    'type'          => $send_message['type'],
                                    'link'          => $send_message[$send_message['type']]['link']
                                ]
                            ]);
                            
                            $last_message_id = $queryMessageRow['message']['id'];
                            
                            $attendant_info = [
                                'number'    => '',
                                'name'      => ''
                            ];
                            
                            $this->model_webhook_messages->addHistory($conversation['id'], $user['user_id'], $last_message_id, $content ?? '', $message->from, $attendant_info['number'], $attendant);
                        } else if ($queryMessageRow['message']['type'] == 'template') {
                            $this->log->write('Mensagem identificada como template...');
                            
                            $send_message['type'] = 'template';
                            
                            $this->log->write(print_r($queryMessageRow['message'], true));
                            
                            $send_message['template']['name'] = explode("|", json_decode($queryMessageRow['message']['message_content'], true)['template']['id'])[1];
                            $send_message['template']['language']['code'] = explode("|", json_decode($queryMessageRow['message']['message_content'], true)['template']['id'])[2];
                            
                             $vars = json_decode($queryMessageRow['message']['message_content'], true)['template']['vars'] ?? array();
                            
                            if (isset(json_decode($queryMessageRow['message']['message_content'], true)['template']['image_url'])) {
                                $send_message['template']['components'][0]['type'] = 'header';
                                $send_message['template']['components'][0]['parameters'][] = array(
                                        'type'  => 'IMAGE',
                                        'image' => ['link'  => json_decode($queryMessageRow['message']['message_content'], true)['template']['image_url']]
                                    );
                            } else {
                                $this->log->write('Condição identificada como falsa.');
                            }
                            
                            if (!empty($vars)) {
                                $send_message['template']['components'][1]['type'] = 'body';
                                
                                foreach($vars as $parameter) {
                                    $parameter = str_replace('[customer_name]', $customer_name, $parameter);
                                     if ((!empty($attendant_info['number'])) && $conversation['attendant_number'] != '') {
                                        $parameter = str_replace('[attendant_name]', $conversation['attendant_name'], $parameter);
                                    }
                                    
                                    $send_message['template']['components'][1]['parameters'][] = array(
                                            'type'      => "TEXT",
                                            'text'      => $parameter
                                        );
                                }
                            }
                        }
                        
                        $last_message_id = 1;
                    }
                    
                    
                    if (isset($sendMessageRow['message']['id'])) {
                        $last_message_id = $sendMessageRow['message']['id'];
                    }
                    
                    $this->log->write(print_r($queryMessageRow['message']['type'], true));
                    
                    if ($queryMessageRow['message']['type'] == 'text') {
                        $send_message['type'] = $queryMessageRow['message']['type'];
                        $send_message[$send_message['type']]['body'] = json_decode($queryMessageRow['message']['message_content'], true)['content'];
                    }
                    
                    if (!$conversation) {
                        $client['customer_number'] = $message->from;
                        $client['customer_name'] = $this->post->contacts[0]->profile->name;
                        
                        $this->model_webhook_messages->add($user['user_id'], $client, $queryMessageRow['message']['id']);
                        $this->model_webhook_messages->addHistory($this->db->getLastId(), $user['user_id'], $queryMessageRow['message']['id'], $content, $message->from, $attendant_info['number'], false);
                    } else {
                        $this->log->write('Informações do Atendante.');
                        $this->log->write(print_r($attendant_info, true));
                        if (empty($attendant_info)) {
                            $attendant_info = [];
                        } else {
                        }
                        if (empty($last_message_id)) {
                            $last_message_id = false;
                        }
                        
                        if ($attendant_info['number'] != '') {
                            $conversation_quantity = $this->model_webhook_messages->getConversationAttendantCount($user['user_id'], $attendant_info);
                        }
                        
                        $this->log->write('Conteúdo do atendente: ' . print_r($attendant_info, true));
                        
                        $data = array(
                            'conversation_id'   => $conversation['id'],
                            'message_id'        => $queryMessageRow['message']['id'] ?? 0
                        );
                        
                        $this->log->write('Conteúdo do atendente: ' . print_r($attendant_info, true));
                        
                        if (empty($attendant_info['number'])) {
                            $attendant_query = $this->model_webhook_messages->getAttendantByConversationId($conversation['id']);
                            
                            $this->log->write('Conteúdo do atendente buscado pelo id da conversa (' . $conversation['id'] . '): ' . print_r($attendant_info, true));
                        } else {
                            $data['attendant'] = $attendant_info;
                        }
                        
                        if (!empty($queryMessageRow['message']['attendant_info_customer_menu'])) {
                            $send_message['to'] = $queryMessageRow['message']['attendant_info_customer_menu'];
                        }
                        
                        $this->model_webhook_messages->edit($data);
                        if (!in_array($conversation['id'], $saved_conversation)) {
                            $this->model_webhook_messages->addHistory($conversation['id'], $user['user_id'], $last_message_id, $content ?? '', $message->from, $attendant_info['number'], $attendant);
                            $saved_conversation[] = $conversation['id'];
                        }
                        
                        if ($customer_menu == 0 && !empty($attendant_info['number'])) {
                            $conversation_quantity2 = $this->model_webhook_messages->getConversationAttendantCount($user['user_id'], $attendant_info);
                            if (($conversation_quantity != $conversation_quantity2) && ($conversation_quantity2 > 1)) {
                                $all_custumer_numbers = $this->model_webhook_messages->getAllCustomerNumbersConversations($user['user_id'], $attendant_info);
                                
                                $this->log->write('Identificadas várias conversas para o mesmo atendente!');
                                $this->log->write('Total de conversas para o mesmo atendente: ' . $conversation_quantity);
                                $this->log->write(print_r($all_custumer_numbers, true));
                                $this->log->write('Conteúdo do atendente: ' . print_r($attendant, true));
                                
                                $queryMessageAppend['message']['type'] = 'interactive';
                                $queryMessageAppend['message']['message_content'] = json_encode(array(
                                    'content'   => $translations['text_message_select_next_to']
                                ));
                                
                                $queryMessageAppend['message']['options'] = array();
                                
                                foreach($all_custumer_numbers as $customer) {
                                    $this->log->write('Conteúdo da variavel $customer loop: ' . print_r($customer, true));
                                    
                                    $queryMessageAppend['message']['options'][] = array(
                                        'id'                    => 'contact_to_' . $customer['customer_number'],
                                        'option_title'          => substr($customer['customer_name'], 0, 23),
                                        'option_description'    => $customer['customer_number']
                                    );
                                    $queryMessageAppend['customer_menu'] = 1;
                                    $queryMessageAppend['message']['attendant_info_customer_menu'] = $all_custumer_numbers[0]['attendant_number'];
                                    
                                    $this->log->write('Conteúdo da variável do menu: ' . print_r($queryMessageAppend['message']['options'], true));
                                }
                                
                                $queryMessage[] = $queryMessageAppend;
                            }
                            
                            $customer_menu = 1;
                        }
                    }
                    
                    // Substitui o nome da variável pelo seu conteúdo e limita o número de caracteres.
                    if (in_array($send_message['type'], ['text', 'interactive'])) {
                        if (!empty($send_message[$send_message['type']]['body'])) {
                            $send_message[$send_message['type']]['body'] = str_replace('[customer_name]', $customer_name, $send_message[$send_message['type']]['body']);
                             if ((!empty($attendant_info['number'])) && $conversation['attendant_number'] != '') {
                                $send_message[$send_message['type']]['body'] = str_replace('[attendant_name]', $conversation['attendant_name'], $send_message[$send_message['type']]['body']);
                            }
                            
                            if ($send_message['type'] == 'text') {
                                $send_message[$send_message['type']]['body'] = substr($send_message[$send_message['type']]['body'], 0, 4095);
                            }
                        }
                    }
                    
                    if (((!empty($attendant_info['number']) || $conversation['attendant_number'] != '') && $conversation['attendant_number'] != $message->from) && ($conversation['status'] == 'started')) {
                        if ($send_message['type'] == 'text') {
                            $send_message[$send_message['type']]['body'] = $translations['text_message_sendded_by_customer'] . $send_message[$send_message['type']]['body'];
                                
                            $send_message[$send_message['type']]['body'] = str_replace('%s', $customer_name, $send_message[$send_message['type']]['body']);
                        } elseif (in_array($send_message['type'], ['image', 'video', 'document'])) {
                            $send_message[$send_message['type']]['caption'] = ($send_message[$send_message['type']]['caption']) ? $translations['text_message_sendded_by_customer'] . $send_message[$send_message['type']]['caption'] : $translations['text_message_sendded_by_customer'];
                            
                            $send_message[$send_message['type']]['caption'] = str_replace('%s', $customer_name,  $send_message[$send_message['type']]['caption']);
                        } elseif (in_array($send_message['type'], ['audio', 'sticker', 'location', 'contacts'])) {
                            $next_send_message = array();
                            
                            $next_send_message['messaging_product'] = 'whatsapp';
                            $next_send_message['to'] = $send_message['to'];
                            $next_send_message['type'] = 'text';
                            
                            $next_send_message['text']['body'] = $translations['text_message_sendded_by_customer'] . $next_send_message['text']['body'];
                                
                            $next_send_message['text']['body'] = str_replace('%s', $customer_name, $next_send_message['text']['body']); 
                        }
                    }
                    
                    $this->log->write('Conteúdo da variável $send_message para disparo: ' . print_r($send_message, true));
            
                    $this->send($send_message);
                    
                    // Se a mensagem que foi enviada fazer parte da lista de espera, a mensagem é excluída.
                    if (!empty($message->queue_id)) {
                        $this->model_webhook_messages->DeleteConversationQueue($message->queue_id);
                    }
                    
                    // Caso exista mensagens extras, as mesmas serão enviadas.
                    if (!empty($next_send_message)) {
                        $this->send($next_send_message);
                        
                        unset($next_send_message);
                    }
                }
            }
        }
        
        private function get_customer_menu($user_id, $attendant_info, $translations) {
            $all_custumer_numbers = $this->model_webhook_messages->getAllCustomerNumbersConversations($user_id, $attendant_info);
            
            $customer_menu['messaging_product'] = 'whatsapp';
            $customer_menu['to'] = $attendant_info['number'];
            $customer_menu['type'] = 'interactive';
            $customer_menu['interactive']['type'] = 'list';
            $customer_menu['interactive']['body']['text'] = $translations['text_message_select_next_to'];
                      
            $this->log->write('Conteúdo da variável $sections: ' . print_r($sections, true));
            
            $send_message[$send_message['type']]['action'] = [
                'button'    => "Menu",
                'sections'  => $sections
            ];
            
            foreach($all_custumer_numbers as $customer) {
                $this->log->write('Conteúdo da variavel $customer loop: ' . print_r($customer, true));
                
                $actions[] = array(
                    'id'             => 'contact_to_' . $customer['customer_number'],
                    'title'          => substr($customer['customer_name'], 0, 23),
                    'description'    => $customer['customer_number']
                );
            }
            
            $sections[] = array(
                'title'     => 'Menu',
                'rows'      => $actions
            );
            
            $customer_menu['interactive']['action'] = [
                'button'    => "Menu",
                'sections'  => $sections
            ];
            
            return $customer_menu;
        }
        
        private function send($data) {
            $ch = curl_init();
        
            curl_setopt($ch, CURLOPT_URL, 'https://' . $this->secure->clear($this->system['api_domain']) . '/' . $this->secure->clear($this->system['api_version']) . '/' . $this->post->metadata->phone_number_id . '/messages');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            
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
            $this->log->write('Conteúdo do disparo: ' . print_r($result, true));
            curl_close($ch);
            
            $results = json_decode($result, true);
            foreach($results['messages'] as $result) {
                $this->log->write('WAMID: ' . $result['id']);
            }
        }
    }
    