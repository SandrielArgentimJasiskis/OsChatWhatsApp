<?php

    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;

    class ModelPagesSchedules extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Model {
        
        public function add($data) {
            $data = json_decode($data, true);
            
            $this->db->query("INSERT INTO `" . DB_PREFIX . "schedules` (`user_id`, `title`, `number`, `file`, `status`, `date`) VALUES ('" . $this->secure->to_int($this->user->getId()) . "', '" . $this->secure->clear($data['title']) . "', '" . $this->secure->clear($data['phone_number']) . "', '" . $this->secure->clear($data['file']['file']['name']) . "', 'wait', '" . $this->secure->clear($data['date']) . "');");
            
            $schedule_id = $this->db->getLastId();
            
            $this->load_model('pages/messages');
            
            $message_template_content = $this->model_pages_messages->getMessage($data['message_template_id']);
            $message_template_content = $message_template_content['message']['message_content'];
            
            $this->db->query("INSERT INTO `" . DB_PREFIX . "schedules_params` (`schedule_id`, `message_template_id`, `params`, `date`) VALUES ('" . $this->secure->to_int($schedule_id) . "', '" . $this->secure->to_int($data['message_template_id']) . "', '" . $this->secure->clear($message_template_content) . "', '" . $this->secure->clear($data['date']) . "')");
            
            $this->log->write(print_r($data['file'], true));
            $numbers = explode("\n", file_get_contents(DIR_MEDIA . 'upload/schedule/' . $data['file']['file']['name']));
            
            foreach($numbers as $number) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "schedules_numbers`(`schedule_id`, `customer_number`) VALUES ('" . $this->secure->to_int($schedule_id) . "', '" . $this->secure->clear($number) . "')");
            }
            
            return $schedule_id;
        }
        
        public function edit($data) {
            $data = json_decode($data, true);
            
            $schedule_id = $data['schedule_id'];
            
            if (!empty($data['file']['file']['name'])) {
                $file = "`file` = '" . $this->secure->escape_sql($data['file']['file']['name']) . "', ";
            } else {
                $file = "";
            }
            
            $this->db->query("UPDATE `" . DB_PREFIX . "schedules` SET `title`= '" . $this->secure->clear($data['title']) . "',`number` = '" . $this->secure->clear($data['phone_number']) . "', " . $file . "`date` = '" . $this->secure->clear($data['date']) . "' WHERE id = '" . $this->secure->to_int($schedule_id) . "' AND user_id = '" . $this->secure->to_int($this->user->getId()) . "'");
            
            $this->load_model('pages/messages');
            
            $message_template_content = $this->model_pages_messages->getMessage($data['message_template_id']);
            $message_template_content = $message_template_content['message']['message_content'];
            
            $this->db->query("INSERT INTO `" . DB_PREFIX . "schedules_params` (`schedule_id`, `message_template_id`, `params`, `date`) VALUES ('" . $this->secure->to_int($schedule_id) . "', '" . $this->secure->to_int($data['message_template_id']) . "', '" . $this->secure->clear($message_template_content) . "', '" . $this->secure->clear($data['date']) . "')");
            
            $this->log->write(print_r($data['file'], true));
            
            if (!empty($data['file']['file']['name'])) {
                $numbers = explode("\n", file_get_contents(DIR_MEDIA . 'upload/schedule/' . $data['file']['file']['name']));
                
                $this->db->query("DELETE FROM " . DB_PREFIX . "schedules_numbers WHERE `schedule_id` = '" . $this->secure->to_int($schedule_id) . "'");
                foreach($numbers as $number) {
                    $this->db->query("INSERT INTO `" . DB_PREFIX . "schedules_numbers`(`schedule_id`, `customer_number`) VALUES ('" . $this->secure->to_int($schedule_id) . "', '" . $this->secure->clear($number) . "')");
                }
            }
        }
        
        public function addStatus($data) {
            $this->log->write("Conteudo a ser cadastrado no historico do agendamento em passa: " . print_r($data, true));
            
            $this->db->query("INSERT INTO `" . DB_PREFIX . "schedules_numbers_status`(`schedule_id`, `customer_number`, `wamid`, `status`, `status_content`, `last_status_temp`) VALUES ('" . $this->secure->to_int($data['schedule_id']) . "', '" . $this->secure->clear($data['number']) . "', '" . $this->secure->clear($data['wamid']) . "', '" . $this->secure->clear($data['status']) . "', '" . $this->secure->clear($data['content']) . "', NOW());");
        }
        
        public function updateStatusByTemp($data) {
            $query = "UPDATE `" . DB_PREFIX . "schedules_numbers_status` SET `status` = '" . $this->secure->clear($data['status']) . "' WHERE status = 'waiting' AND last_status_temp < DATE_SUB(NOW(), INTERVAL " . $this->secure->clear($data['temp']) . ");";
            
            $this->log->write("Query para atualizar o status das mensagens dos agendamentos em massa no período de 24 horas (aguardando).");
            
            $this->db->query($query);
        }
        
        public function cancel($schedule_id) {
            $this->db->query("UPDATE `" . DB_PREFIX . "schedules` SET `status`= 'cancelled' WHERE id = '" . $this->secure->to_int($schedule_id) . "' AND user_id = '" . $this->secure->to_int($this->user->getId()) . "'");
        }
        
        public function pause($schedule_id) {
            $this->db->query("UPDATE `" . DB_PREFIX . "schedules` SET `status`= 'paused' WHERE id = '" . $this->secure->to_int($schedule_id) . "' AND user_id = '" . $this->secure->to_int($this->user->getId()) . "'");
        }
        
        public function play($schedule_id) {
            $this->db->query("UPDATE `" . DB_PREFIX . "schedules` SET `status`= 'unpaused' WHERE id = '" . $this->secure->to_int($schedule_id) . "' AND user_id = '" . $this->secure->to_int($this->user->getId()) . "'");
        }
        
        public function getSchedule($schedule_id) {
            $query_schedule = $this->db->query("SELECT *, params FROM " . DB_PREFIX . "schedules INNER JOIN " . DB_PREFIX . "schedules_params ON (" . DB_PREFIX . "schedules.id = " . DB_PREFIX . "schedules_params.schedule_id) WHERE " . DB_PREFIX . "schedules.id = '" . $this->secure->to_int($schedule_id) . "' AND user_id = '" . $this->secure->to_int($this->user->getId()) . "'");
            
            if ($query_schedule['num_rows'] == 0) {
                return false;
            }
            
            $schedule_return = array();
            $query_numbers = $this->db->query("SELECT customer_number FROM " . DB_PREFIX . "schedules_numbers WHERE schedule_id = '" . $this->secure->to_int($query_schedule['rows'][0]['id']) . "'");
                
            $schedule_return = array(
                'content'   => $query_schedule['rows'][0],
                'numbers'   => $query_numbers['rows']
            );
            
            return $schedule_return;
        }
        
        public function getScheduleStatus($schedule_id) {
            $query_schedule = $this->db->query("SELECT status FROM " . DB_PREFIX . "schedules WHERE " . DB_PREFIX . "schedules.id = '" . $this->secure->to_int($schedule_id) . "' AND user_id = '" . $this->secure->to_int($this->user->getId()) . "'");
            
            return $query_schedule['rows'][0]['status'] ?? false;
        }
        
        public function getSchedulesNumberStatus($schedule_id) {
           $schedule_return = array();
            $query_numbers = $this->db->query("SELECT customer_number, status, status_content FROM " . DB_PREFIX . "schedules_numbers_status WHERE schedule_id = '" . $this->secure->to_int($schedule_id) . "'");
            
            return $query_numbers['rows'];
        }
        
        public function getActiveSchedules() {
            $query_schedules = $this->db->query("SELECT *, params FROM " . DB_PREFIX . "schedules INNER JOIN " . DB_PREFIX . "schedules_params ON (" . DB_PREFIX . "schedules.id = " . DB_PREFIX . "schedules_params.schedule_id) WHERE status != 'finished' AND status != 'paused' AND status != 'cancelled' AND " . DB_PREFIX . "schedules.date < NOW();");
            
            if ($query_schedules['num_rows'] == 0) {
                return false;
            }
            
            $schedules_return = array();
            foreach($query_schedules['rows'] as $schedule) {
                $query_numbers = $this->db->query("SELECT customer_number FROM " . DB_PREFIX . "schedules_numbers WHERE schedule_id = '" . $this->secure->to_int($schedule['id']) . "' AND (schedule_id, customer_number) NOT IN (SELECT schedule_id, customer_number FROM " . DB_PREFIX . "schedules_numbers_status) LIMIT 50;");
                
                $schedules_return[] = array(
                    'content'   => $schedule,
                    'numbers'   => $query_numbers['rows']
                );
            }
            
            return $schedules_return;
        }
        
        public function finishSchedule($schedule_id) {
            $this->db->query("UPDATE `" . DB_PREFIX . "schedules` SET `status`= 'finished' WHERE id = '" . $this->secure->to_int($schedule_id) . "'");
        }
        
        public function getUserSchedules($user_id) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "schedules WHERE user_id = '" . $this->secure->to_int($user_id) . "'");
            
            return ($query['num_rows'] >= 1) ? $query['rows'] : false;
        }
    }
    