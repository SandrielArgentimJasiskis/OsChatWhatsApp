<?php

	namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
	
	class ControllerExtensionsDashboardChartMostMessageType extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
	    
		public function index($data) {
			return $this->load_view('extensions/dashboard/chart_most_message_type_form', $this->secure->remove_tags($data, '<i>'));
		}
		
		public function dashboard($data) {
		    $this->load_model('pages/messages');
            
            $data['totals'] = $this->model_pages_messages->getMostMessageType();
            
            return $this->load_view('extensions/dashboard/chart_most_message_type_info', $this->secure->remove_tags($data));
		}
	}
	