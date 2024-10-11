<?php

	namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
	
	class ControllerExtensionsDashboardMessageReportStatus extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
	    
		public function index($data) {
			return $this->load_view('extensions/dashboard/message_report_status_form', $this->secure->remove_tags($data, '<i>'));
		}
		
		public function dashboard($data) {
		    $this->load_model('pages/messages');
			
		    $totals = $this->model_pages_messages->getTotalMessagesByStatus('sent', $data['range']);
            
            $data['message_status_sent']['total'] = 0;
            foreach ($totals as $message_sent) {
                $data['message_status_sent']['total'] += (int)$message_sent['total'];
            }
            
            $totals = $this->model_pages_messages->getTotalMessagesByStatus('read', $data['range']);
            
            $data['message_status_read']['total'] = 0;
            foreach ($totals as $message_read) {
                $data['message_status_read']['total'] += (int)$message_read['total'];
            }
            
            return $this->load_view('extensions/dashboard/message_report_status_info', $this->secure->remove_tags($data));
		}
	}
	