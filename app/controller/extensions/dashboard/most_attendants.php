<?php

	namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
	
	class ControllerExtensionsDashboardMostAttendants extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
	    
		public function index($data) {
			return $this->load_view('extensions/dashboard/most_attendants_form', $this->secure->remove_tags($data, '<i>'));
		}
		
		public function dashboard($data) {
		    $this->load_model('pages/messages');
            
            $most_attendants = array_reverse($this->model_pages_messages->getMostAttendants($data['limit']));
            
            if ($most_attendants) {
                foreach($most_attendants as $key => $most_attendant) {
                    $data['most_attendants'][] = array(
                        'attendant'     => $most_attendant['attendant_name'],
                        'total'         => $most_attendant['total']
                    );
                }
            } else {
                $data['most_attendants'] = false;
            }
            
            return $this->load_view('extensions/dashboard/most_attendants_info', $this->secure->remove_tags($data));
		}
	}
	