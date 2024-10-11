<?php

	namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
	
	class ControllerExtensionsDashboardLastConversations extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
	    
		public function index($data) {
			return $this->load_view('extensions/dashboard/last_conversations_form', $this->secure->remove_tags($data, '<i>'));
		}
		
		public function dashboard($data) {
		    $this->load_model('pages/messages');
		    
		    $last_conversations = $this->model_pages_messages->getLastConversations($data['limit']);
            if ($last_conversations) {
                foreach($last_conversations as $last_conversation) {
                    $data['last_conversations'][] = array(
                        'customer'      => $last_conversation['customer_name'],
                        'attendant'   => $last_conversation['attendant_name'],
                        'date'      => date_format(date_create($last_conversation['date']), 'd/m/Y h:i:s')
                    );
                }
            } else {
                $data['last_conversations'] = false;
            }
            
            return $this->load_view('extensions/dashboard/last_conversations_info', $this->secure->remove_tags($data));
		}
	}
	