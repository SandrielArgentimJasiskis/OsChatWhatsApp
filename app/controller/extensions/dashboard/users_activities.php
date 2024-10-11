<?php

	namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
	
	class ControllerExtensionsDashboardUsersActivities extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
	    
		public function index($data) {
			return $this->load_view('extensions/dashboard/users_activities_form', $this->secure->remove_tags($data, '<i>'));
		}
		
		public function dashboard($data) {
		    $this->load_model('pages/users');
			
		    $activities = $this->model_pages_users->getActivities($data['limit']);
		    
		     if ($activities) {
    		    foreach($activities as $activity) {
    		        if (in_array($activity['activity'], ['login', 'logout'])) {
    		            $text_activity = sprintf($data['text_' . $activity['activity']], $activity['user']);
    		        } else if ($activity['activity'] == 'page_view') {
    		            $text_activity = sprintf($data['text_page_view'], $activity['user'], json_decode($activity['content'], true)['current_url'], json_decode($activity['content'], true)['route']);
    		        }
    		        
    		        $data['users_activities'][] = array(
    		            'user'      => $activity['user'],
    		            'activity'  => $text_activity,
    		            'date'      => date_format(date_create($activity['date']), 'd/m/Y h:i:s')
    		        );
    		    }
		    } else {
                $data['users_activities'] = false;
            }
            
            return $this->load_view('extensions/dashboard/users_activities_info', $this->secure->remove_tags($data, '<a>'));
		}
	}
	