<?php

	namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
	
	class ControllerExtensionsDashboardLastKeywords extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
	    
		public function index($data) {
			return $this->load_view('extensions/dashboard/last_keywords_form', $this->secure->remove_tags($data, '<i>'));
		}
		
		public function dashboard($data) {
		    $this->load_model('pages/messages');
		    
		    $last_keywords = $this->model_pages_messages->getLastKeywords($data['limit']);
		    
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
		    
		    return $this->load_view('extensions/dashboard/last_keywords_info', $this->secure->remove_tags($data));
		}
		
		public function number_encode($number) {
		$number = '+' . substr($number, 0, 2) . ' (' . substr($number, 2, 2) . ') ' . substr($number, 4, -4) . '-' . substr($number, -4);
		
		return $number;
            }
	}
	