<?php

	namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
	
	class ControllerExtensionsReportsLastMessagesTypeMedia extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
	    
		public function index($data) {
			return $this->load_view('extensions/reports/last_messages_type_media_form', $this->secure->remove_tags($data, '<i>'));
		}
		
		public function report($data) {
		    $this->load_model('pages/messages');
		    
		    $last_medias = $this->model_pages_messages->getLastMessagesTypeMedia(500);
            if ($last_medias) {
                foreach($last_medias as $last_media) {
                    $data['last_medias'][] = array(
                        'number'      => $this->number_encode($last_media['from']),
                        'type'      => $data[sprintf('text_message_type_media_%s', $last_media['type'])],
                        'url'   => $last_media['url'],
                        'date'      => date_format(date_create($last_media['date']), 'd/m/Y h:i:s')
                    );
                }
            } else {
                $data['last_medias'] = false;
            }
            
            return $this->load_view('extensions/reports/last_messages_type_media_info', $this->secure->remove_tags($data));
		}
		
		public function export($data) {
		    
		    $this->load_model('pages/messages');
		    
		    $last_medias = $this->model_pages_messages->getLastMessagesTypeMedia(500);
            if ($last_medias) {
                foreach($last_medias as $last_media) {
                    $data['last_medias'][] = array(
                        'number'    => $this->number_encode($last_media['from']),
                        'type'      => $data[sprintf('text_message_type_media_%s', $last_media['type'])],
                        'url'       => $last_media['url'],
                        'date'      => date_format(date_create($last_media['date']), 'd/m/Y h:i:s')
                    );
                }
            } else {
                $data['last_medias'] = false;
            }
            
            $file = fopen('Report.csv', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            
            fputcsv($file, [
                $data['text_customer'], $data['text_type'], $data['text_url'], $data['text_time']
            ], ";");
            
            if ($data['last_medias']) {
                foreach($data['last_medias'] as $last_media) {
                    fputcsv($file, $last_media, ";");
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
	