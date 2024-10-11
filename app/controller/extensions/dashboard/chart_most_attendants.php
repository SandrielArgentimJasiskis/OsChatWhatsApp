<?php

	namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
	
	class ControllerExtensionsDashboardChartMostAttendants extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
	    
		public function index($data) {
			return $this->load_view('extensions/dashboard/chart_most_attendants_form', $this->secure->remove_tags($data, '<i>'));
		}
		
		public function dashboard($data) {
		    $this->load_model('pages/messages');
            
            $most_attendants = $this->model_pages_messages->getMostAttendants(5);
            
            $data['total'] = count($most_attendants) ?? 0;
            
            foreach($most_attendants as $key => $most_attendant) {
                $data['most_attendants'][] = array(
                    'id'                => $this->secure->to_int($key),
                    'attendant_name'    => $most_attendant['attendant_name'],
                    'total'             => $most_attendant['total']
                );
            }
            
            return $this->load_view('extensions/dashboard/chart_most_attendants_info', $this->secure->remove_tags($data));
		}
	}
	