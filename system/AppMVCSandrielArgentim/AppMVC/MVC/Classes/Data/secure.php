<?php

    namespace AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Data;
    
    class Secure {
		
		use \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Traits\Call;
		use \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Traits\Constructor;
		use \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Traits\Load;
		
		public function access_denied() {
		    $this->log->write('AVISO: POSSÍVEL TENTATIVA DE INVASÃO DETECTADA:');
		    $this->log->write('DADOS DA INVASÃO:');
		    $this->log->write('URL ATUAL: ' . $this->url->get());
		    $this->log->write('REFERÊNCIA: ' . $this->request->server['HTTP_REFERER'] ?? '');
		    $this->log->write('ROTA: ' . $this->request->get['route'] ?? 'pages/dashboard');
		    $this->log->write('USER AGENT: ' . $this->request->server['HTTP_USER_AGENT']);
		    $this->log->write('IP DO INVASOR: ' . $this->request->server['REMOTE_ADDR'] ?? '');
		    $this->log->write('CONTEÚDO DA SESSÃO DO USUÁRIO: ' . print_r($this->request->session, true));
		    
		    header("HTTP/1.1 405 Method Not Allowed");
            die();
		}
        
        public function clear($data) {
            return $this->escape_sql($this->remove_tags($data));
        }
        
        public function escape_sql($data) {
            return addslashes($data);
        }
        
        public function remove_tags($data, $tags = '', $ignore = array()) {
            if (!is_array($data)) {
                return strip_tags(html_entity_decode($data), $tags);
            }
            
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = $this->remove_tags($value, $tags, $ignore);
                } else {
                    if (!in_array($key, $ignore) && !is_object($value)) {
                        $data[$key] = strip_tags(html_entity_decode($value), $tags);
                    }
                }
            }
            
            return $data;
        }
        
        public function get_access_area() {
            $ignore = ['common/footer', 'common/header', 'common/history', 'common/menu', 'common/styles', 'cron/auto_schedules', 'cron/finished_conversations', 'pages/error', 'pages/forgotten', 'pages/login', 'pages/logout', 'pages/dashboard', 'webhook/whatsapp'];
            
            return $this->get_directory_content(DIR_APP . 'controller/', DIR_APP . 'controller/', $ignore);
        }
        
        public function get_directory_content($directory, $base_directory, $ignore = array()) {
            $directory_content = scandir($directory);
            
            $data = [];
            
            foreach ($directory_content as $content) {
                if (!in_array($content, ['.', '..']) && !in_array(str_replace($base_directory, "", $directory . str_replace(".php", "", $content)), $ignore)) {
                    if (is_dir($directory . $content)) {
                        $areas = $this->get_directory_content($directory . $content . '/', $base_directory, $ignore);
                        
                        foreach($areas as $area) {
                            $data[] = $area;
                        }
                    } else {
                        $data[]['area'] = str_replace($base_directory, "", $directory . str_replace(".php", "", $content));
                    }
                }
            }
            
            return $data;
        }
        
        public function is_app_id($data) {
            return (strlen($data) < 14 || strlen($data) > 32) ? false : true;
        }
        
        public function is_attendant_name($data) {
            return (strlen($data) < 3 || strlen($data) > 64) ? false : true;
        }
		
		public function is_conversation_duration($conversation_duration) {
			$conversation_duration = explode(' ', $conversation_duration);
			
			if (empty($conversation_duration[1])) {
				return false;
			}
			if (!$this->is_int($conversation_duration[0])) {
				return false;
			}
			
			$conversation_duration[1] = strtoupper($conversation_duration[1]);
			
			if (!in_array($conversation_duration[1], ['SECOND', 'MINUTE', 'HOUR'])) {
				return false;
			}
			
			return true;
		}
        
        public function is_fullname($data) {
            return (strlen($data) < 8 || strlen($data) > 255) ? false : true;
        }
        
        public function is_int($data) {
            return ((int)$data != 0) ? true : false;
        }
        
        public function is_user($data) {
            return (strlen($data) < 4 || strlen($data) > 32) ? false : true;
        }
        
        public function is_pass($data) {
            return preg_match('/^(?=.*[a-z])/', $data)  // verifica se tem pelo menos uma letra minúscula
                && preg_match('/^(?=.*[A-Z])/', $data)  // verifica se tem pelo menos uma letra maiúscula
                && preg_match('/^(?=.*[0-9])/', $data)  // verifica se tem pelo menos um número
                && preg_match('/[^a-zA-Z0-9]/', $data)  // verifica se tem pelo menos um caractere especial
                && preg_match('/^.{8,}$/', $data);      // verifica se tem 8 ou mais caracteres
        }
        
        public function is_phone($data) {
            if (preg_replace('/[^0-9]/', '', $data) != $data) {
                return false;
            }
            
            return (strlen($data) < 11 || strlen($data) > 20) ? false : true;
        }
        
        public function is_token($data) {
            return (strlen($data) < 100) ? false : true;
        }
        
        public function is_date_range($data) {
            return (in_array($data, ['yesterday', 'day', 'week', 'month', 'year']));
        }
        
        public function is_whatsapp_business_account_id($data) {
            return (strlen($data) < 14 || strlen($data) > 32) ? false : true;
        }
        
        public function is_message_title($data) {
            return (strlen($data) < 3 || strlen($data) > 24) ? false : true;
        }
        
        public function is_message_type($data) {
            $types = ['text', 'image', 'interactive', 'media', 'template'];
            
            return (in_array($data, $types));
        }
                
        public function is_message_media_type($data) {
            $types = ['image', 'video', 'audio', 'document'];
            
            return (in_array($data, $types));
        }
        
        public function is_message_event($data) {
            $events = ['', 'init', 'response', 'started_by_attendant', 'finished_by_attendant', 'finished_by_customer', 'timeout'];
            
            return (in_array($data, $events));
        }
        
        public function is_message_option_title($data) {
            return (strlen($data) < 3 || strlen($data) > 24) ? false : true;
        }
        
        public function is_message_option_description($data) {
            return (strlen($data) < 3 || strlen($data) > 72) ? false : true;
        }
        
        public function is_schedule_title($data) {
            return (strlen($data) < 3 || strlen($data) > 24) ? false : true;
        }
        
        public function is_txt_file($file) {
            return (mime_content_type($file) == "text/plain") ? true : false;
        }
        
        public function is_url($data) {
            if (!filter_var($data, FILTER_VALIDATE_URL)) {
                return false;
            }
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $data);
            curl_setopt($ch, CURLOPT_NOBODY, 1);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 400);
            
            $content = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            $this->log->write('Codigo de resposta da url: ' . $httpcode);
            
            return ( $httpcode == 200 ) ? true : false;
        }
        
        public function hash($data) {
            return hash('sha512', $data);
        }
        
        public function has_access($area, $access_method, $user_info) {
            $access_area = $this->get_access_area();
            
            $access_verify = 0;
            foreach($access_area as $access_area) {
                if ($access_area['area'] == $area) {
                    $access_verify = 1;
                }
            }
            
            if (!$access_verify) {
                return true;
            }
            
            $user_access = json_decode($user_info['access']);
            
            if (empty($user_access->$access_method)) {
                return false;
            }
            
            $user_access = $user_access->$access_method;
            
            return (in_array($area, $user_access));
        }
        
        public function verify() {
            if (empty($this->request->session['user_id'])) {
                if (($this->route->get() != 'pages/login') && ($this->route->get() != 'pages/forgotten') && substr($this->route->get(), 0, 8) != 'webhook/' && substr($this->route->get(), 0, 5) != 'cron/') {
                    return false;
                }
            }
            
            return true;
        }
        
        public function logout() {
            $this->session->destroy('user_id');
            $this->session->destroy('user_use_id');
        }
        
        public function random($length = 128) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $randomCode = '';
            
            for ($i = 0; $i < $length; $i++) {
                $randomCode .= $characters[$this->randint(0, strlen($characters) - 1)];
            }
            
            return $randomCode;
        }
		
		public function randint($min = 0, $max = 999) {
			return random_int($min, $max);
		}
        
        public function to_int($data) {
            return (int)$data;
        }
    }
    