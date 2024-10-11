<?php

	namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
	
	class ControllerExtensionsReportsAutoSchedules extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
	    
		public function index($data) {
			return $this->load_view('extensions/reports/auto_schedules_form', $this->secure->remove_tags($data, '<i>'));
		}
		
		public function report($data) {
		    $this->load_model('pages/schedules');
		    
		    if (empty($this->request->get['view'])) {
		        $auto_schedules = $this->model_pages_schedules->getUserSchedules($this->user->getId());
		    
		        $data['auto_schedules'] = false;
		        
                if ($auto_schedules) {
                    foreach($auto_schedules as $auto_schedule) {
                        $data['schedules'][] = array(
                            'id'                => $this->secure->to_int($auto_schedule['id']),
                            'title'             => $auto_schedule['title'],
                            'status'            => $auto_schedule['status'],
                            'text_status'       => $this->data[sprintf('text_status_%s', $auto_schedule['status'])],
                            'date'              => date_format(date_create($auto_schedule['date']), 'd/m/Y h:i:s'),
                            'view'              => $this->url->link('pages/reports/edit', '&extension_id=' . $this->secure->to_int($data['id']) . '&view=' . $this->secure->to_int($auto_schedule['id']))
                        );
                    }
                }
                
                return $this->load_view('extensions/reports/auto_schedules_report_list', $this->secure->remove_tags($data));
		    } else {
		        $auto_schedule_info = $this->model_pages_schedules->getSchedule($this->request->get['view'])['content'];
		        
                $data['schedule_info'] = array(
                    'id'                => $this->secure->to_int($auto_schedule_info['id']),
                    'title'             => $auto_schedule_info['title'],
                    'status'            => $auto_schedule_info['status'],
                    'text_status'       => $this->data[sprintf('text_status_%s', $auto_schedule_info['status'])],
                    'date'              => date_format(date_create($auto_schedule_info['date']), 'd/m/Y h:i:s'),
                    'view'              => $this->url->link('pages/reports/edit', '&extension_id=' . $this->secure->to_int($data['id']) . '&view=' . $this->secure->to_int($auto_schedule_info['id']))
                );
		        
		        $schedule_numbers_status = $this->model_pages_schedules->getSchedulesNumberStatus($this->request->get['view']);
		        
		        $data['$schedule_numbers_status'] = array();
		        foreach($schedule_numbers_status as $number_status) {
		            $status_content = '';
		            if ($number_status['status'] == 'failed') {
		                $status_content = json_decode($number_status['status_content'], true);
		            }
		            
		            $data['schedule_numbers_status'][] = array(
		                'number'            => $number_status['customer_number'],
		                'status'            => $number_status['status'],
		                'text_status'       => $this->data[sprintf('text_status_%s', $number_status['status'])],
		                'status_content'    => $status_content
	                );
		        }
		        
		        return $this->load_view('extensions/reports/auto_schedules_report_report', $this->secure->remove_tags($data));
		    }
        }
            
        public function export($data) {
		    $this->load_model('pages/schedules');
		    
		    if (empty($this->request->get['view'])) {
		        $auto_schedules = $this->model_pages_schedules->getUserSchedules($this->request->session['user_id']);
		    
		        $data['auto_schedules'] = false;
		        
                if ($auto_schedules) {
                    foreach($auto_schedules as $auto_schedule) {
                        $data['auto_schedules'][] = array(
                            'id'                => $this->secure->to_int($auto_schedule['id']),
                            'title'             => $auto_schedule['title'],
                            'text_status'       => $this->data[sprintf('text_status_%s', $auto_schedule['status'])],
                            'date'              => date_format(date_create($auto_schedule['date']), 'd/m/Y h:i:s')
                        );
                    }
                }
                
    		    $file = fopen('Report.csv', 'w');
               fwrite($file, "\xEF\xBB\xBF");
        
                fputcsv($file, [
                    $data['text_th_id'], $data['text_th_schedule'], $data['text_th_status'], $data['text_th_date']
                ], ";");
                
                if ($data['auto_schedules']) {
                    foreach($data['auto_schedules'] as $auto_schedule) {
                        fputcsv($file, $auto_schedule, ";");
                    }
                    
                    fclose($file);
                    
                    header("Content-Transfer-Encoding: UTF-8");
                    header("Content-Description: File Transfer"); 
                    header("Content-Type: application/csv"); 
                    header("Content-Disposition: attachment; filename=\"Report.csv\""); 
                    
                    readfile('Report.csv');
                    die('');
                }
		    } else {
		        $auto_schedule_info = $this->model_pages_schedules->getSchedule($this->request->get['view'])['content'];
		        
                $data['schedule_info'] = array(
                    'id'                => $this->secure->to_int($auto_schedule_info['id']),
                    'title'             => $auto_schedule_info['title'],
                    'status'            => $auto_schedule_info['status'],
                    'text_status'       => $this->data[sprintf('text_status_%s', $auto_schedule_info['status'])],
                    'date'              => date_format(date_create($auto_schedule_info['date']), 'd/m/Y h:i:s'),
                    'view'              => $this->url->link('pages/reports/edit', '&extension_id=' . $this->secure->to_int($data['id']) . '&view=' . $this->secure->to_int($auto_schedule_info['id']))
                );
		        
		        $schedule_numbers_status = $this->model_pages_schedules->getSchedulesNumberStatus($this->request->get['view']);
		        
		        $data['$schedule_numbers_status'] = array();
		        foreach($schedule_numbers_status as $number_status) {
		            $status_content = '';
		            if ($number_status['status'] == 'failed') {
		                $status_content = json_decode($number_status['status_content'], true);
		            }
		            
		            $data['schedule_numbers_status'][] = array(
		                'number'            => $number_status['customer_number'],
		                'status'            => $number_status['status'],
		                'text_status'       => $this->data[sprintf('text_status_%s', $number_status['status'])],
		                'status_content'    => $status_content
	                );
		        }
		        
		        /* Obtém o tema atual */
		        $this->load_model('pages/system');
    			$theme_id = $this->model_pages_system->get()['default_theme_id'];
    			$themes = $this->model_pages_system->getThemes();
    			
    			foreach($themes as $theme) {
    				if ($theme['id'] == $theme_id) {
    					$data['path'] = URL_APP . 'app/view/theme/' . $theme['path'];
    				}
    			}
		        
		        // Configurar os cabeçalhos HTTP para download
                header("Content-type: text/html");
                header("Content-Disposition: attachment; filename=Report.html");
		        
		        $this->template->display($this->load_view('extensions/reports/auto_schedules_report_report', $this->secure->remove_tags($data)));
		        die('');
		    }
		}
		
		public function number_encode($number) {
		$number = '+' . substr($number, 0, 2) . ' (' . substr($number, 2, 2) . ') ' . substr($number, 4, -4) . '-' . substr($number, -4);
		
		return $number;
            }
	}
	