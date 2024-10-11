<?php
    
    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
    
    class ModelWebhookMessages extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Model {
        
        public function add($user_id, $client, $message_id) {
            $query = $this->db->query("INSERT INTO " . DB_PREFIX . "conversations (user_id, customer_number, customer_name, message_id, last_message_temp) VALUES ('" . $this->secure->to_int($user_id) . "', '" . $this->secure->clear($client['customer_number']) . "', '" . $this->secure->clear($client['customer_name']) . "', '" . $this->secure->to_int($message_id) . "', NOW())");
        }
        
        public function addNextSend($user_id, $to_number) {
            $query = 
            "DELETE FROM `" . DB_PREFIX . "conversations_next_send` WHERE user_id = '" . $this->secure->to_int($user_id) . "'";
            
            $this->log->write('Query para deletar o próximo número que receberá as mensagens: ');
            $this->log->write($query);
            
            $this->db->query($query);
            
            $query = "INSERT INTO `" . DB_PREFIX . "conversations_next_send`(`user_id`, `to_number`) VALUES ('" . $this->secure->to_int($user_id) . "', '" . $this->secure->clear($to_number) . "')";
            
            $this->log->write('Query para inserir o próximo número que receberá as mensagens: ');
            $this->log->write($query);
            
            $this->db->query($query);
        }
        
        public function addHistory($conversation_id, $user_id, $message_id, $message_response, $client_number, $attendant = ['number' => '', 'name' => '']) {
            $query_message_id = $this->secure->to_int($message_id);
            
            $query_attendant = (!empty($attendant['number'])) ? "'" . $this->secure->clear($attendant['number']) . "'," : "'',";
            $query_attendant .= (!empty($attendant´['name'])) ? "'" . $this->secure->clear($attendant['name']) . "'" : "''";
            
            $query = $this->db->query("INSERT INTO `" . DB_PREFIX . "conversations_history`(`conversation_id`, `user_id`, `message_id`, `message_response`, `customer_number`, `attendant_number`, `attendant_name`, `last_message_temp`) VALUES ('" . $this->secure->to_int($conversation_id) . "', '" . $this->secure->to_int($user_id) . "', " . $query_message_id . ", '" . $this->secure->clear($message_response) . "',  '" . $this->secure->clear($client_number) . "', "  . $query_attendant . ", NOW())");
        }
        
        public function addStatus($status, $phone) {
            $query = $this->db->query("INSERT INTO `" . DB_PREFIX . "conversations_messages_status` (`from`, `status`, `date`) VALUES ('" . $this->secure->clear($phone) . "', '" . $this->secure->clear($status) . "', NOW())");
        }
        
        public function updateWamidStatus($data) {
            $query = "UPDATE `" . DB_PREFIX . "schedules_numbers_status` SET `status`= 'success' WHERE wamid = '" . $this->secure->clear($data['wamid']) . "'";
            
            $this->log->write("Query para atualizar o status dos números do agendamento em massa: " . $query);
            
            $this->db->query($query);
        }
        
        public function addConversationQueue($data) {
            $query = $this->db->query("INSERT INTO `" . DB_PREFIX . "conversations_queue`(`conversation_id`, `user_id`,`customer_number`, `customer_name`, `attendant_number`, `attendant_name`,  `message`) VALUES ('" . $this->secure->to_int($data['conversation_id']) . "', '" . $this->secure->to_int($data['user_id']) . "', '" . $this->secure->clear($data['customer_number']) . "', '" . $this->secure->clear($data['customer_name']) . "', '" . $this->secure->clear($data['attendant_number']) . "', '" . $this->secure->clear($data['attendant_name']) . "', '" . $this->secure->clear($data['message']) . "')");
        }
        
        public function addAttendantToConversation($conversation_id, $attendant_info) {
            $this->db->query("UPDATE `" . DB_PREFIX . "conversations` SET `attendant_number` = '" . $this->secure->clear($attendant_info['number']) . "',`attendant_name` = '" . $this->secure->clear($attendant_info['name']) . "' WHERE id = '" . $this->secure->to_int($conversation_id) . "';");
        }
        
        public function edit($data = array()) {
            $conversation_id = $data['conversation_id'];
            $message_id = $data['message_id'] ?? false;
            $attendant = $data['attendant'] ?? false;
            
            $query_message_id = ($message_id) ? "message_id = '" . $this->secure->to_int($message_id) . "', " : "";
            $query_attendant = ($attendant) ? "attendant_number = '" . $this->secure->clear($attendant['number']) . "', attendant_name = '" . $this->secure->clear($attendant['name']) . "', " : "";
            
            $query = $this->db->query("UPDATE " . DB_PREFIX . "conversations SET " . $query_message_id . $query_attendant . "last_message_temp = NOW() WHERE id = '" . $this->secure->to_int($conversation_id) . "'");
        }
        
        public function initConversation($conversation_id) {
            $this->db->query("UPDATE `" . DB_PREFIX . "conversations` SET `status`= 'started' WHERE id = '" . $this->secure->to_int($conversation_id) . "'");
        }
        
        public function finishConversation($conversation_id) {
            $this->db->query("UPDATE `" . DB_PREFIX . "conversations` SET `status`= 'finished' WHERE id = '" . $this->secure->to_int($conversation_id) . "'");
        }
        
        public function ConversationChangeStatus($conversation_id, $status) {
            $this->db->query("UPDATE `" . DB_PREFIX . "conversations` SET `status`= '" . $this->secure->clear($status) . "' WHERE id = '" . $this->secure->to_int($conversation_id) . "'");
        }
        
        public function DeleteConversationQueue($queue_id) {
            $query = $this->db->query("DELETE FROM `" . DB_PREFIX . "conversations_queue` WHERE id = '" . $this->secure->to_int($queue_id) . "'");
        }
        
        public function getConversation($user_id, $from) {
            $conversation_quantity = $this->getConversationCount($user_id, $from);
            
            if ($conversation_quantity <= 1) {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "conversations WHERE user_id = '" . $this->secure->to_int($user_id) . "' AND attendant_number = '" . $this->secure->escape_sql($from) . "' AND last_message_temp >= DATE_SUB(NOW(), INTERVAL " . $this->getConversationDuration() . ") AND status != 'finished' LIMIT 1");
                
                if ($query['num_rows'] == '0') {
                    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "conversations WHERE user_id = '" . $this->secure->to_int($user_id) . "' AND customer_number = '" . $this->secure->escape_sql($from) . "' AND last_message_temp >= DATE_SUB(NOW(), INTERVAL " . $this->getConversationDuration() . ") AND status != 'finished' LIMIT 1");
                }
            } else {
                $this->log->write('Identificadas várias conversas para o mesmo atendente.');
                $to = $this->getNextSend($user_id);
                $this->log->write('Próximo número para envio identificado como: ' . print_r($to, true));
                
                $this->log->write('Número que enviou a mensagem: ' . $from);
                $this->log->write('Número que receberá a mensagem: ' . $to);
                
                if (!$to) {
                    $query['num_rows'] = 0;
                    
                    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "conversations WHERE user_id = '" . $this->secure->to_int($user_id) . "' AND attendant_number = '" . $this->secure->escape_sql($from) . "' AND last_message_temp >= DATE_SUB(NOW(), INTERVAL " . $this->getConversationDuration() . ") AND status != 'finished' LIMIT 1");
                    
                    return array(
                        'customer_menu'     => true,
                        'attendant_number'  => $from,
                        'attendant_name'    => $query->rows[0]['attendant_name'] ?? ''
                    );
                    return ['customer_menu' => true, 'to' => $from];
                } else {
                    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "conversations WHERE user_id = '" . $this->secure->to_int($user_id) . "' AND attendant_number = '" . $this->secure->escape_sql($to) . "' AND last_message_temp >= DATE_SUB(NOW(), INTERVAL " . $this->getConversationDuration() . ") AND status != 'finished' LIMIT 1");
                    
                    if ($query['num_rows'] == '0') {
                        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "conversations WHERE user_id = '" . $this->secure->to_int($user_id) . "' AND customer_number = '" . $this->secure->escape_sql($to) . "' AND last_message_temp >= DATE_SUB(NOW(), INTERVAL " . $this->getConversationDuration() . ") AND status != 'finished' LIMIT 1");
                    }
                }
            }
            
            return ($query['num_rows'] == '1') ? $query['rows'][0] : false;
        }
        
        public function getConversationById($conversation_id) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "conversations WHERE id = '" . $this->secure->to_int($conversation_id) . "' AND last_message_temp >= DATE_SUB(NOW(), INTERVAL " . $this->getConversationDuration() . ") AND status != 'finished' LIMIT 1");
            
            return $query['rows'][0] ?? false;
        }
        
        public function getConversationQueueByCustomerNumber($from) {
            $query = $this->db->query("SELECT " . DB_PREFIX . "conversations_queue.id, " . DB_PREFIX . "conversations_queue.message FROM " . DB_PREFIX . "conversations_queue INNER JOIN " . DB_PREFIX . "conversations ON " . DB_PREFIX . "conversations.id = " . DB_PREFIX . "conversations_queue.conversation_id WHERE " . DB_PREFIX . "conversations_queue.customer_number = '" . $this->secure->escape_sql($from) . "' AND " . DB_PREFIX . "conversations.last_message_temp >= DATE_SUB(NOW(), INTERVAL " . $this->getConversationDuration() . ") AND " . DB_PREFIX . "conversations.status != 'finished' LIMIT 100");
            $this->log->write('Resultado da query para obter as mensagens da conversa informada: ' . print_r($query, true));
            
            $queue = array();
            foreach($query['rows'] as $key => $message) {
                $queue[] = json_decode($message['message']);
                $queue[$key]['queue_id'] = $message['id'];
            }
            
            return $queue;
        }
        
        public function getConversationsByTimeOut() {
            $query = "SELECT * FROM " . DB_PREFIX . "conversations WHERE last_message_temp <= DATE_SUB(NOW(), INTERVAL " . $this->getConversationDuration() . ") AND status != 'finished'";
            
            $this->log->write('Query para buscar as conversas que já foram encerradas: ' . $query);
            
            $query = $this->db->query($query);
            
            return $query['rows'];
        }
        
        public function getConversationCountByCustomerNumber($user_id, $from) {
            $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "conversations WHERE user_id = '" . $this->secure->to_int($user_id) . "' AND customer_number = '" . $this->secure->escape_sql($from) . "' AND last_message_temp >= DATE_SUB(NOW(), INTERVAL " . $this->getConversationDuration() . ") AND status != 'finished' LIMIT 1");
                
            if ($query['rows'][0]['total'] == '0') {
                $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "conversations WHERE user_id = '" . $this->secure->to_int($user_id) . "' AND customer_number = '" . $this->secure->escape_sql($from) . "' AND last_message_temp >= DATE_SUB(NOW(), INTERVAL " . $this->getConversationDuration() . ") AND status != 'finished' LIMIT 1");
            }
            
            return $query['rows'][0]['total'];
        }
        
        public function getAllCustomerNumbersConversations($user_id, $attendant = false) {
            $query_attendant = ($attendant) ? "attendant_number = '" . $this->secure->escape_sql($attendant['number']) . "', attendant_name = '" . $this->secure->escape_sql($attendant['name']) . "', " : "";
            
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "conversations WHERE user_id = '" . $this->secure->to_int($user_id) . "' AND last_message_temp >= DATE_SUB(NOW(), INTERVAL " . $this->getConversationDuration() . ") AND status = 'started'");
                
            if ($query['num_rows'] == '0') {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "conversations WHERE user_id = '" . $this->secure->to_int($user_id) . "' " . $attendant_query . " AND last_message_temp >= DATE_SUB(NOW(), INTERVAL " . $this->getConversationDuration() . ") AND status = 'started'");
            }
            
            return $query['rows'];
        }
        
        public function getConversationCount($user_id, $from) {
            $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "conversations WHERE user_id = '" . $this->secure->to_int($user_id) . "' AND attendant_number = '" . $this->secure->escape_sql($from) . "' AND last_message_temp >= DATE_SUB(NOW(), INTERVAL " . $this->getConversationDuration() . ") AND status != 'finished'");
            
            if ($query['num_rows'] == '0') {
                $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "conversations WHERE user_id = '" . $this->secure->to_int($user_id) . "' AND customer_number = '" . $this->secure->escape_sql($from) . "' AND last_message_temp >= DATE_SUB(NOW(), INTERVAL " . $this->getConversationDuration() . ") AND status != 'finished'");
            }
            
            return $query['rows'][0]['total'];
        }
        
        public function getConversationAttendantCount($user_id, $attendant = false) {
            $query_attendant = ($attendant) ? "AND attendant_number = '" . $this->secure->escape_sql($attendant['number']) . "' AND attendant_name = '" . $this->secure->escape_sql($attendant['name']) . "'" : "";
            $query = "SELECT COUNT(*) as total FROM " . DB_PREFIX . "conversations WHERE user_id = '" . $this->secure->to_int($user_id) . "' " . $query_attendant . " AND last_message_temp >= DATE_SUB(NOW(), INTERVAL " . $this->getConversationDuration() . ") AND status = 'started'";
            
            $this->log->write('Query para obter a quantidade de atendentes na conversa.');
            $this->log->write($query);
            
            $query = $this->db->query($query);
            
            return $query['rows'][0]['total'];
        }
        
        public function getAttendantByConversationId($conversation_id) {
            $query = "SELECT attendant_number as number, attendant_name as name FROM " . DB_PREFIX . "conversations WHERE id = '" . $this->secure->to_int($conversation_id) . "'";
            
            $this->log->write('Query para buscar o atendente pelo id da conversa: ' . $query);
            
            $query = $this->db->query($query);
            
            return $query['rows'][0];
        }
		
		public function getConversationDuration() {
			$this->load_model('pages/system');
			
			$configs = $this->model_pages_system->get();
			$this->log->write(print_r($configs, true));
			
			return $configs['conversation_duration'];
		}
        
        public function getMessageByEvent($user_id, $event, $content = false, $what_is = '') {
            $query = "SELECT " . DB_PREFIX . "messages.*";
            if ($what_is == "option") {
                $query .= ($content) ?  " FROM " . DB_PREFIX . "messages INNER JOIN " . DB_PREFIX . "messages_responses ON " . DB_PREFIX . "messages.id = " . DB_PREFIX . "messages_responses.message_id AND event_content LIKE '%\"option_id\":\"" . $this->secure->escape_sql($content) . "\"%' WHERE " : " FROM " . DB_PREFIX . "messages ";
            } elseif ($what_is == "text") {
                $query .= ($content) ?  ", " . DB_PREFIX . "messages_keywords.keyword FROM " . DB_PREFIX . "messages INNER JOIN " . DB_PREFIX . "messages_keywords ON " . DB_PREFIX . "messages.id = " . DB_PREFIX . "messages_keywords.message_id WHERE " . DB_PREFIX . "messages_keywords.keyword LIKE '" . $this->secure->escape_sql($content) . "' AND " : " FROM " . DB_PREFIX . "messages";
            } else {
                $query .= ' FROM ' . DB_PREFIX . 'messages WHERE ';
            }
            
            if ($event != 'init') {
                $query .= "event = '" . $this->secure->escape_sql($event) . "'";
            } else {
                $query .= "is_init = 'on'";
            }
            $query .= "AND user_id = '" . $this->secure->to_int($user_id) . "' AND status = '1'";
            
            $this->log->write($query);
            $query = $this->db->query($query);
            foreach($query['rows'] as $result) { 
            
                $message_id = $result['id'] ?? false;
                
                $query_options = $this->getMessageOptions($message_id);
                
                $query_keywords = $this->getMessageKeywords($message_id);
                
                    $result['options'] = $query_options;
                    $result['keywords'] = $query_keywords;
                    
                    $message[] = array(
                        'message'   => $result ?? false,
                    );
            }
            
            return $message ?? false;
        }
        
        public function getNextSend($user_id) {
            $query = $this->db->query("SELECT to_number as `to` FROM " . DB_PREFIX . "conversations_next_send WHERE user_id = '" . $this->secure->to_int($user_id) . "'");
            
            return ($query['num_rows'] == '1') ? $query['rows'][0]['to'] : false;
        }
        
        public function getMessageKeywords($message_id) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "messages_keywords WHERE message_id = '" . $this->secure->to_int($message_id) . "' ORDER BY id");
            
            return $query['rows'];
        }
        
        public function getMessageOptions($message_id) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "messages_options WHERE message_id = '" . $this->secure->to_int($message_id) . "' ORDER BY option_sort_order");
            
            return $query['rows'];
        }
        
        public function getMessageType($message_id) {
            $query = $this->db->query("SELECT type FROM " . DB_PREFIX . "messages WHERE id = '" . $this->secure->to_int($message_id) . "' LIMIT 1");
            
            return ($query['num_rows'] == '1') ? $query['rows'][0]['type'] : false;
        }
        
        public function getMessageOption($option_id) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "messages_options WHERE id = '" . $this->secure->to_int($option_id) . "'");
            
            return ($query['num_rows'] >= 1) ? $query['rows'] : false;
        }
        
        public function messageKeywordExists($keyword) {
		    $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "messages_keywords WHERE keyword = '" . $this->secure->escape_sql($keyword) . "'");
		    
		    return ($query['rows'][0]['total'] == 1) ? $query['rows'][0]['total'] : false;
		}
    }
    