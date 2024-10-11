<?php

	namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
	
	class ControllerExtensionsReportsLastKeywords extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
	    
		public function index($data) {
			return $this->load_view('extensions/reports/last_keywords_form', $this->secure->remove_tags($data, '<i>'));
		}
		
		public function report($data) {
		    $this->load_model('pages/messages');
		    
		    $last_keywords = $this->model_pages_messages->getLastKeywords(500);
		    
		    if ($last_keywords) {
    		    foreach($last_keywords as $last_keyword) {
    		        $data['last_keywords'][] = array(
    		            'keyword'  => $last_keyword['keyword'],
    		            'customer' => $this->number_encode($last_keyword['customer']),
    		            'date'      => date_format(date_create($last_keyword['date']), 'd/m/Y h:i:s')
    		        );
    		    }
		    } else {
                $data['last_conversations'] = false;
                }
                
                return $this->load_view('extensions/reports/last_keywords_report', $this->secure->remove_tags($data));
            }
            
            public function export($data) {
		    $this->load_model('pages/messages');
		    
		    $last_keywords = $this->model_pages_messages->getLastKeywords(500);
		    
		    if ($last_keywords) {
    		    foreach($last_keywords as $last_keyword) {
    		        $data['last_keywords'][] = array(
    		            'keyword'  => $last_keyword['keyword'],
    		            'customer' => $this->number_encode($last_keyword['customer']),
    		            'date'      => date_format(date_create($last_keyword['date']), 'd/m/Y h:i:s')
    		        );
    		    }
		    } else {
                $data['last_keywords'] = false;
            }
		    
		    $file = fopen('Report.csv', 'w');
                   fwrite($file, "\xEF\xBB\xBF");
            
            fputcsv($file, [
                $data['text_keyword'], $data['text_customer'], $data['text_time']
            ], ";");
            
            if ($data['last_keywords']) {
                foreach($data['last_keywords'] as $last_keyword) {
                    fputcsv($file, $last_keyword, ";");
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
	