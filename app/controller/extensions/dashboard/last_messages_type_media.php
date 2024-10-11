<?php

	namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
	
	class ControllerExtensionsDashboardLastMessagesTypeMedia extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
	    
		public function index($data) {
			return $this->load_view('extensions/dashboard/last_messages_type_media_form', $this->secure->remove_tags($data, '<i>'));
		}
		
		public function dashboard($data) {
		    $this->load_model('pages/messages');
		    
		    $last_medias = $this->model_pages_messages->getLastMessagesTypeMedia($data['limit']);
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
            
            return $this->load_view('extensions/dashboard/last_messages_type_media_info', $this->secure->remove_tags($data));
		}
		
		public function number_encode($number) {
            $number = '+' . substr($number, 0, 2) . ' (' . substr($number, 2, 2) . ') ' . substr($number, 4, -4) . '-' . substr($number, -4);
            
            return $number;
        }
	}
	