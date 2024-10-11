<?php

	namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
	
	class ControllerExtensionsDashboardProblemSolving extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
	    
		public function index($data) {
			return $this->load_view('extensions/dashboard/problem_solving_form', $this->secure->remove_tags($data, '<i>'));
		}
		
		public function dashboard($data) {
		    $this->load_model('pages/messages');
			
		    $data['totals'] = $this->model_pages_messages->getTotalProblemSolving($this->request->session['user_id']);
            
            return $this->load_view('extensions/dashboard/problem_solving_info', $this->secure->remove_tags($data));
		}
	}
	