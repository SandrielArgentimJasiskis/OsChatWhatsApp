<?php

	namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
	
	class ControllerExtensionsDashboardMostKeywords extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
	    
		public function index($data) {
			return $this->load_view('extensions/dashboard/most_keywords_form', $this->secure->remove_tags($data, '<i>'));
		}
		
		public function dashboard($data) {
		    $this->load_model('pages/messages');
		    
		    $most_keywords = $this->model_pages_messages->getMostKeywords($data['limit']);
		    
		    if ($most_keywords) {
    		    foreach($most_keywords as $most_keyword) {
    		        $data['most_keywords'][] = array(
    		            'keyword'  => json_decode($most_keyword['message_response'], true)['keyword_content'] ?? '',
    		            'quantity'  => $this->secure->to_int($most_keyword['quantity'])
    		        );
    		    }
		    } else {
                $data['most_conversations'] = false;
            }
		    
		    return $this->load_view('extensions/dashboard/most_keywords_info', $this->secure->remove_tags($data));
		}
	}
	