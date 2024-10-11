<?php

	namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
	
	class ControllerExtensionsDashboardChartMessages extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
	    
		public function index($data) {
			return $this->load_view('extensions/dashboard/chart_messages_form', $this->secure->remove_tags($data, '<i>'));
		}
		
		public function dashboard($data) {
		    $this->load_model('pages/messages');
		    
		    if ($data['range'] == 'yesterday') {
                $iterable = 23;
            } elseif ($data['range'] == 'day') {
                $iterable = 23;
            } elseif ($data['range'] == 'week') {
                $iterable = 7;
            } elseif ($data['range'] == 'month') {
                $iterable = 31;
            } elseif ($data['range'] == 'year') {
                $iterable = 12;
            }
            
            $totals = $this->model_pages_messages->getTotalMessagesByStatus('sent', $data['range']);
            
            for ($i = 1; $i <= $iterable; $i++) {
                $data['message_status_sent']['data'][] = $this->set_value($totals, $i-1);
            }
            
            $totals = $this->model_pages_messages->getTotalMessagesByStatus('read', $data['range']);
            
            for ($i = 1; $i <= $iterable; $i++) {
                $data['message_status_read']['data'][] = $this->set_value($totals, $i-1);
            }
            
            for ($i = 1; $i <= $iterable; $i++) {
                $data['xaxis'][] = [$i, $i];
            }
            
            return $this->load_view('extensions/dashboard/chart_messages_info', $this->secure->remove_tags($data));
		}
		
		public function set_value($totals, $i) {
            foreach($totals as $key => $total) {
                if ($total['day'] == $i) {
                    return [$total['day']-1, $total['total']];
                }
            }
            
            return [$i, 0];
        }
	}
	