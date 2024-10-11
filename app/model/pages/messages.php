<?php

    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
    
    class ModelPagesMessages extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Model {
        
        public function add($data) {
            $data = json_decode($data, true);
            
            $this->log->write('Conteúdo da mensagem para ser adicionada' . print_r($data, true));
            
            $settings = $this->load('pages/settings', 'model', 'getSettings', (array)$this->user->getId());
            $phone = $settings['phone'];
            
            $is_init = $data['is_init'] ?? 0;
            
            $this->db->query("INSERT INTO " . DB_PREFIX . "messages (user_id, status, message_title, type, from_number, is_init, finish, is_self, attendant_id, event, message_content) VALUES ('" . $this->secure->to_int($this->user->getId()) . "', '" . $this->secure->to_int($data['status']) . "', '" . $this->secure->clear($data['message_title']) . "', '" . $this->secure->clear($data['type']) . "', '" . $this->secure->clear($phone) . "', '" . $this->secure->clear($is_init) . "', '" . $this->secure->to_int($data['finish']) . "', '" . $this->secure->to_int($data['is_self']) . "', '" . $this->secure->to_int($data['attendant_id']) . "', '" . $this->secure->clear($data['event']) . "', '" . $this->secure->clear(json_encode($data['message_content'], JSON_UNESCAPED_UNICODE)) . "');");
            
            $message_id = $this->db->getLastId();
            
            $this->db->query("DELETE FROM " . DB_PREFIX . "messages_options WHERE message_id = '" . $this->secure->to_int($message_id) . "'");
            
            if (!empty($data['message_option'])) {
                foreach($data['message_option'] as $message_option) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "messages_options (message_id, option_title, option_description, option_sort_order) VALUES ('" . $this->secure->to_int($message_id) . "', '" . $this->secure->clear($message_option['option_title']) . "', '" . $this->secure->clear($message_option['option_description']) . "', '" . $this->secure->to_int($message_option['option_sort_order']) . "');");
                }
            }
            
            $this->db->query("DELETE FROM " . DB_PREFIX . "messages_responses WHERE message_id = '" . $this->secure->to_int($message_id) . "'");
            
            if (!empty($data['message_response'])) {
                foreach($data['message_response'] as $message_response) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "messages_responses (message_id, event_content) VALUES ('" . $this->secure->to_int($message_id) . "', '" . $this->secure->clear(json_encode($message_response)) . "');");
                }
            }
            
            $this->db->query("DELETE FROM " . DB_PREFIX . "messages_keywords WHERE message_id = '" . $this->secure->to_int($message_id) . "'");
            
            $data['message_keyword'] = explode(",", $data['message_keyword']);
            if (!empty($data['message_keyword'])) {
                foreach($data['message_keyword'] as $message_keyword) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "messages_keywords (message_id, keyword) VALUES ('" . $this->secure->to_int($message_id) . "', '" . $this->secure->clear($message_keyword) . "');");
                }
            }
            
            return $message_id;
        }
        
        public function edit($data) {
            $data = json_decode($data, true);
            
            $this->db->query("UPDATE " . DB_PREFIX . "messages SET status = '" . $this->secure->to_int($data['status']) . "', message_title = '" . $this->secure->clear($data['message_title']) . "', type = '" . $this->secure->clear($data['type']) . "', is_init = '" . $this->secure->clear($data['is_init']) . "', finish = '" . $this->secure->to_int($data['finish']) . "', is_self = '" . $this->secure->to_int($data['is_self']) . "', attendant_id = '" . $this->secure->to_int($data['attendant_id']) . "', event = '". $this->secure->clear($data['event']) . "', message_content = '" . $this->secure->clear(json_encode($data['message_content']), JSON_UNESCAPED_UNICODE) . "' WHERE id = '" . $this->secure->to_int($data['id']) . "'");
            
            $this->db->query("DELETE FROM " . DB_PREFIX . "messages_responses WHERE message_id = '" . $this->secure->to_int($message_id) . "'");
            
            $this->db->query("DELETE FROM " . DB_PREFIX . "messages_options WHERE message_id = '" . $this->secure->to_int($data['id']) . "'");
            
            if (!empty($data['message_option'])) {
                foreach($data['message_option'] as $message_option) {
                    if ($message_option['id'] == '') {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "messages_options (message_id, option_title, option_description, option_sort_order) VALUES ('" . $this->secure->to_int($data['id']) . "', '" . $this->secure->clear($message_option['option_title']) . "', '" . $this->secure->clear($message_option['option_description']) . "', '" . $this->secure->to_int($message_option['option_sort_order']) . "');");
                    } else {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "messages_options (id, message_id, option_title, option_description, option_sort_order) VALUES ('" . $this->secure->to_int($message_option['id']) . "', '" . $this->secure->to_int($data['id']) . "', '" . $this->secure->clear($message_option['option_title']) . "', '" . $this->secure->clear($message_option['option_description']) . "', '" . $this->secure->to_int($message_option['option_sort_order']) . "');");
                    }
                }
            }
            
            $this->db->query("DELETE FROM " . DB_PREFIX . "messages_responses WHERE message_id = '" . $this->secure->to_int($data['id']) . "'");
            
            if (!empty($data['message_response'])) {
                foreach($data['message_response'] as $message_response) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "messages_responses (message_id, event_content) VALUES ('" . $this->secure->to_int($data['id']) . "', '" . $this->secure->clear(json_encode($message_response)) . "');");
                }
            }
            
            $this->db->query("DELETE FROM " . DB_PREFIX . "messages_keywords WHERE message_id = '" . $this->secure->to_int($data['id']) . "'");
            
            $data['message_keyword'] = explode(",", $data['message_keyword']);
            if (!empty($data['message_keyword'])) {
                foreach($data['message_keyword'] as $message_keyword) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "messages_keywords (message_id, keyword) VALUES ('" . $this->secure->to_int($data['id']) . "', '" . $this->secure->clear($message_keyword) . "');");
                }
            }
        }
        
        public function delete($message_id) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "messages WHERE id = '" . $this->secure->to_int($message_id) . "'");
            
            $this->db->query("DELETE FROM " . DB_PREFIX . "messages_options WHERE message_id = '" . $this->secure->to_int($message_id) . "'");
            
            $this->db->query("DELETE FROM " . DB_PREFIX . "messages_responses WHERE message_id = '" . $this->secure->to_int($message_id) . "'");
            
            $this->db->query("DELETE FROM " . DB_PREFIX . "messages_keywords WHERE message_id = '" . $this->secure->to_int($message_id) . "'");
        }
        
        public function getUserMessages($user_id) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "messages WHERE user_id = '" . $this->secure->to_int($user_id) . "'");
            
            return ($query['num_rows'] != '0') ? $query['rows'] : false;
        }
        
        public function getTotalProblemSolving($user_id) {
            $query_bot = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "conversations WHERE user_id = '" . $this->secure->to_int($user_id) . "' AND attendant_number = '' AND status = 'finished'");
            
            $query_attendant = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "conversations WHERE user_id = '" . $this->secure->to_int($user_id) . "' AND attendant_number != '' AND status = 'finished'");
            
            return array(
                'bot'           => $query_bot['rows'][0]['total'],
                'attendant'     => $query_attendant['rows'][0]['total']
            );
        }
        
        public function getTotalUserMessages($user_id) {
            $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "messages WHERE user_id = '" . $this->secure->to_int($user_id) . "'");
            
            return $query['rows'][0]['total'];
        }
        
        public function getMessage($message_id) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "messages WHERE id = '" . $this->secure->to_int($message_id) . "' LIMIT 1");
            
            $query_options = $this->getMessageOptions($message_id);
            
            $query_responses = $this->getMessageResponses($message_id);
            
            $messages_responses = array();
            foreach($query_responses as $query_response) {
                $messages_responses[] = array(
                    'id'            => $query_response['id'],
                    'message_id'    => $query_response['message_id'],
                    'option_id'     => $query_response['option_id'],
                    'options'       => $this->getMessageOptions($query_response['message_id'])
                );
            }
            
            $query_keywords = implode(",", $this->getMessageKeywords($message_id));
            
            $message = array(
                'message'           => $query['rows'][0] ?? false,
                'options'           => $query_options,
                'responses'         => $messages_responses,
                'keyword'           => $query_keywords
            );
            
            return $message;
        }
        
        public function getMessageKeywords($message_id) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "messages_keywords WHERE message_id = '" . $this->secure->to_int($message_id) . "' ORDER BY id");
            
            $data = array();
            foreach($query['rows'] as $keyword) {
                $data[] = $keyword['keyword'];
            }
            
            return $data;
        }
        
        public function getMessageOptions($message_id) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "messages_options WHERE message_id = '" . $this->secure->to_int($message_id) . "' ORDER BY option_sort_order ASC");
            
            return $query['rows'];
        }
        
        public function getMessageResponses($message_id) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "messages_responses WHERE message_id = '" . $this->secure->to_int($message_id) . "' ORDER BY id");
            
            $responses = array();
            
            if ($query['num_rows'] == '0') {
                return $responses;
            }
            
            foreach($query['rows'] as $row) {
                $responses[] = array(
                    'id'            => $row['id'],
                    'message_id'    => json_decode($row['event_content'], true)['message_id'],
                    'option_id'     => json_decode($row['event_content'], true)['option_id']
                );
            }
            
            return $responses;
        }
        
        public function getTotalMessagesByStatus($status, $range) {
            if ($range == "yesterday") {
                $data_range = "HOUR(date)";
                $query_range = " AND DATE(DATE) = DATE(DATE_SUB(NOW(), INTERVAL 1 DAY)) GROUP BY HOUR(date)";
            } elseif ($range == "day") {
                $data_range = "HOUR(date)";
                $query_range = " AND DATE(date) = DATE(NOW()) GROUP BY HOUR(date)";
            } elseif ($range == "week") {
                $date_start = strtotime('-' . date('w') . ' days');
                
                $data_range = "WEEKDAY(date)+1";
                $query_range = " AND DATE(date) >= DATE('" . date('Y-m-d', $date_start) . "') GROUP BY WEEKDAY(date)";
            } elseif ($range == "month") {
                $data_range = "DAY(date)";
                $query_range = " AND MONTH(date) = MONTH(NOW()) AND YEAR(date) = YEAR(NOW()) GROUP BY DAY(date)";
            } elseif ($range == "year") {
                $data_range = "MONTH(date)";
                $query_range = " AND YEAR(date) = YEAR(NOW()) GROUP BY MONTH(date)";
            }
            
            $query = $this->db->query("SELECT COUNT(id) AS total, " . $data_range . " AS day FROM `" . DB_PREFIX . "conversations_messages_status` WHERE status = '" . $this->secure->escape_sql($status) . "'" . $query_range);
            
            return $query['rows'];
        }
        
        
        public function getLastConversations($limit = 1) {
            $query_conversations = $this->db->query("SELECT customer_name, attendant_name, last_message_temp AS date FROM `" . DB_PREFIX . "conversations` WHERE attendant_name != '' AND attendant_number != '' AND user_id = '" . $this->secure->to_int($this->user->getId()) . "' ORDER BY `last_message_temp` DESC LIMIT " . $this->secure->to_int($limit));
            
            return ($query_conversations['num_rows'] >= 1) ? $query_conversations['rows'] : false;
        }
        
        public function getLastKeywords($limit = 1) {
            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "conversations_history` WHERE `message_response` LIKE '%{\"type\":\"keyword\",%' AND attendant_number = '' AND user_id = '" . $this->secure->to_int($this->user->getId()) . "' ORDER BY `last_message_temp` DESC LIMIT " . $this->secure->to_int($limit));
            
            foreach($query['rows'] as $key => $media) {
                $query['rows'][$key] = array(
                    'customer'  => $media['customer_number'],
                    'keyword'   => json_decode($media['message_response'], true)['keyword_content'] ?? '',
                    'date'      => $media['last_message_temp']
                );
            }
            
            return $query['rows'] ?? array();
        }
        
        public function getLastMessagesTypeMedia($limit = 1) {
            $query_medias = $this->db->query("SELECT * FROM `" . DB_PREFIX . "conversations_history` WHERE `message_response` LIKE '%{\"type\":\"media\",%' AND attendant_number = '' AND user_id = '" . $this->secure->to_int($this->user->getId()) . "' ORDER BY `last_message_temp` DESC LIMIT " . $this->secure->to_int($limit));
            
            foreach($query_medias['rows'] as $key => $media) {
                $query['rows'][$key] = array(
                    'from'      => $media['customer_number'],
                    'type'   => json_decode($media['message_response'], true)['media_content']['type'] ?? '',
                    'url'   => json_decode($media['message_response'], true)['media_content']['link'] ?? '',
                    'date'      => $media['last_message_temp']
                );
            }
            
            return $query['rows'] ?? array();
        }
        
        public function getLastNumbers($limit = 1) {
            $query_numbers = $this->db->query("SELECT message_response, customer_number, last_message_temp AS date FROM `" . DB_PREFIX . "conversations_history` WHERE `message_response` LIKE '%{\"message_option_id\":%' ORDER BY `last_message_temp` DESC LIMIT " . $this->secure->to_int($limit));
            
            foreach($query_numbers['rows'] as $key => $number) {
                $query['rows'][$key] = array(
                    'from'      => $number['customer_number'],
                    'subject'   => json_decode($number['message_response'], true)['message_option_content'][0]['option_title'] ?? '',
                    'date'      => $number['date']
                );
            }
            
            return $query['rows'] ?? array();
        }
        
        public function getMostAttendants($limit = 1) {
            $query = $this->db->query("SELECT attendant_name, attendant_number, COUNT(*) as total FROM `" . DB_PREFIX . "conversations` WHERE attendant_name != '' AND attendant_number != '' and user_id = '" . $this->secure->to_int($this->user->getId()) . "' GROUP BY attendant_number ORDER BY COUNT(*) ASC LIMIT " . $this->secure->to_int($limit));
            
            return $query['rows'];
        }
        
        public function getMostKeywords($limit = 1) {
            $query = $this->db->query("SELECT message_response, COUNT(*) as quantity FROM `" . DB_PREFIX . "conversations_history` WHERE `message_response` LIKE '%{\"type\":\"keyword\",%' AND attendant_number = '' GROUP BY message_response HAVING COUNT(*) >= 1 ORDER BY COUNT(*) DESC LIMIT " . $this->secure->to_int($limit));
            
            return ($query['num_rows'] >= 1) ? $query['rows'] : false;
        }
        
        public function getMostMessageType() {
            $query_text_total = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "conversations_history` WHERE `message_response` LIKE '%{\"text\":%' AND attendant_number = ''");
            
            $query_interactive_total = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "conversations_history` WHERE `message_response` LIKE '%{\"type\":\"interactive\",%' AND attendant_number = ''");
            
            $query_media_total = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "conversations_history` WHERE `message_response` LIKE '%{\"type\":\"media\",%' AND attendant_number = ''");
            
            return array(
                'text'          => $query_text_total['rows'][0]['total'],
                'interactive'   => $query_interactive_total['rows'][0]['total'],
                'media'         => $query_media_total['rows'][0]['total']
            );
        }
        
        public function getMostMessageTypeMedia() {
            $query_image_total = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "conversations_history` WHERE `message_response` LIKE '{\"type\":\"media\",\"media_content\":{\"type\":\"image\",%' AND attendant_number = ''");
            
            $query_video_total = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "conversations_history` WHERE `message_response` LIKE '{\"type\":\"media\",\"media_content\":{\"type\":\"video\",%' AND attendant_number = ''");
            
            $query_audio_total = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "conversations_history` WHERE `message_response` LIKE '{\"type\":\"media\",\"media_content\":{\"type\":\"audio\",%' AND attendant_number = ''");
            
            $query_document_total = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "conversations_history` WHERE `message_response` LIKE '{\"type\":\"media\",\"media_content\":{\"type\":\"document\",%' AND attendant_number = ''");
            
            return array(
                'image'     => $query_image_total['rows'][0]['total'],
                'video'     => $query_video_total['rows'][0]['total'],
                'audio'     => $query_audio_total['rows'][0]['total'],
                'document'  => $query_document_total['rows'][0]['total']
            );
        }
        
        public function getMostOptionMessages($limit = 1) {
            $query = $this->db->query("SELECT message_response, COUNT(*) as total FROM `" . DB_PREFIX . "conversations_history` WHERE `message_response` LIKE '%{\"message_option_id\":%' GROUP BY message_response ASC ORDER BY COUNT(*) DESC LIMIT " . $this->secure->to_int($limit));
            
            foreach($query['rows'] as $key => $total) {
                $query['rows'][$key] = array(
                    'subject'   => json_decode($total['message_response'], true)['message_option_content'][0]['option_title'] ?? '',
                    'total'      => $total['total']
                );
            }
            
            return $query['rows'] ?? array();
        }
        
        public function messageInitExists($user_id, $message_id) {
			$query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "messages WHERE user_id = '" . $this->secure->to_int($user_id) . "' AND id != '" . $this->secure->to_int($message_id) . "' AND is_init = 'on'");
			
			return $query['rows'][0]['total'];
		}
    }
    