<?php

	namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
	
	class ControllerExtensionsReportsLastMessages extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
	    
		public function index($data) {
			return $this->load_view('extensions/reports/last_messages_form', $this->secure->remove_tags($data, '<i>'));
		}
		
		public function report($data) {
		    $this->load_model('pages/messages');
		    
		    $last_contacts = $this->model_pages_messages->getLastNumbers(500);
            if ($last_contacts) {
                
                foreach($last_contacts as $last_contact) {
                    $data['last_contacts'][] = array(
                        'number'      => $this->number_encode($last_contact['from']),
                        'subject'   => $last_contact['subject'],
                        'date'      => date_format(date_create($last_contact['date']), 'd/m/Y h:i:s')
                    );
                }
            } else {
                $data['last_contacts'] = false;
            }
            
            return $this->load_view('extensions/reports/last_messages_report', $this->secure->remove_tags($data));
		}
		
		public function export($data) {
		    
		    $this->load_model('pages/messages');
		    
		    $last_contacts = $this->model_pages_messages->getLastNumbers(500);
            if ($last_contacts) {
                foreach($last_contacts as $last_contact) {
                    $data['last_contacts'][] = array(
                        'number'      => $this->number_encode($last_contact['from']),
                        'subject'   => $last_contact['subject'],
                        'date'      => date_format(date_create($last_contact['date']), 'd/m/Y h:i:s')
                    );
                }
            } else {
                $data['last_contacts'] = false;
            }
            
            $file = fopen('Report.csv', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            
            fputcsv($file, [
                $data['text_number'], $data['text_subject'], $data['text_time']
            ], ";");
            
            if ($data['last_contacts']) {
                foreach($data['last_contacts'] as $last_contact) {
                    fputcsv($file, $last_contact, ";");
                }
                
                fclose($file);
                
                header("Content-Transfer-Encoding: UTF-8");
                header("Content-Description: File Transfer"); 
                header("Content-Type: application/csv"); 
                header("Content-Disposition: attachment; filename=\"Report.csv\""); 
                
                readfile('Report.csv');
                die('');
            } else {
                $this->session->data['error'] = $data['text_empty_data'];
                
                $this->url->redirect('pages/reports/edit', '&extension_id=' . $data['id']);
            }
		}
		
		public function number_encode($number) {
            $number = '+' . substr($number, 0, 2) . ' (' . substr($number, 2, 2) . ') ' . substr($number, 4, -4) . '-' . substr($number, -4);
            
            return $number;
        }
	}
	