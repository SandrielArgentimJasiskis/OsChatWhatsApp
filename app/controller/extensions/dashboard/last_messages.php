<?php

	namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
	
	class ControllerExtensionsDashboardLastMessages extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
	    
		public function index($data) {
			return $this->load_view('extensions/dashboard/last_messages_form', $this->secure->remove_tags($data, '<i>'));
		}
		
		public function dashboard($data) {
		    $this->load_model('pages/messages');
		    
		    $last_contacts = $this->model_pages_messages->getLastNumbers($data['limit']);
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
            
            return $this->load_view('extensions/dashboard/last_messages_info', $this->secure->remove_tags($data));
		}
		
		public function number_encode($number) {
            $number = '+' . substr($number, 0, 2) . ' (' . substr($number, 2, 2) . ') ' . substr($number, 4, -4) . '-' . substr($number, -4);
            
            return $number;
        }
	}
	