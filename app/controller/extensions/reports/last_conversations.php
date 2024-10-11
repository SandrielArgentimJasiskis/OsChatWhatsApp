<?php

	namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
	
	class ControllerExtensionsReportsLastConversations extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
	    
		public function index($data) {
			return $this->load_view('extensions/reports/last_conversations_form', $this->secure->remove_tags($data, '<i>'));
		}
		
		public function report($data) {
		    $this->load_model('pages/messages');
		    
		    $last_conversations = $this->model_pages_messages->getLastConversations(500);
		    
                    if ($last_conversations) {
                        foreach($last_conversations as $last_conversation) {
                            $data['last_conversations'][] = array(
                                'customer'      => $last_conversation['customer_name'],
                                'attendant'   => $last_conversation['attendant_name'],
                                'date'      => date_format(date_create($last_conversation['date']), 'd/m/Y h:i:s')
                            );
                         }
                    } else {
                        $data['last_conversations'] = false;
                    }
                
                return $this->load_view('extensions/reports/last_conversations_report', $this->secure->remove_tags($data));
            }
            
            public function export($data) {
		    $this->load_model('pages/messages');
		    
		    $last_conversations = $this->model_pages_messages->getLastConversations(500);
		    
            if ($last_conversations) {
                foreach($last_conversations as $last_conversation) {
                    $data['last_conversations'][] = array(
                        'customer'      => $last_conversation['customer_name'],
                        'attendant'   => $last_conversation['attendant_name'],
                        'date'      => date_format(date_create($last_conversation['date']), 'd/m/Y h:i:s')
                    );
                 }
            } else {
                $data['last_conversations'] = false;
            }
		    
		    $file = fopen('Report.csv', 'w');
                   fwrite($file, "\xEF\xBB\xBF");
            
            fputcsv($file, [
                $data['text_customer'], $data['text_attendant'], $data['text_time']
            ], ";");
            
            if ($data['last_conversations']) {
                foreach($data['last_conversations'] as $last_conversation) {
                    fputcsv($file, $last_conversation, ";");
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
	