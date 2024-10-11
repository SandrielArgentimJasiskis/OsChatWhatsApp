<?php

	namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
	
	class ControllerExtensionsDashboardMostMessages extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
	    
		public function index($data) {
			return $this->load_view('extensions/dashboard/most_messages_form', $this->secure->remove_tags($data, '<i>'));
		}
		
		public function dashboard($data) {
		    $this->load_model('pages/messages');
		    
		    $most_option_messages = $this->model_pages_messages->getMostOptionMessages($data['limit']);
            if ($most_option_messages) {
                foreach($most_option_messages as $most_option_message) {
                    $data['most_option_messages'][] = array(
                        'subject'   => $most_option_message['subject'],
                        'total'     => $most_option_message['total']
                    );
                }
            } else {
                $data['most_option_messages'] = false;
            }
            
            return $this->load_view('extensions/dashboard/most_messages_info', $this->secure->remove_tags($data));
		}
		
		public function number_encode($number) {
            $number = '+' . substr($number, 0, 2) . ' (' . substr($number, 2, 2) . ') ' . substr($number, 4, -4) . '-' . substr($number, -4);
            
            return $number;
        }
	}
	