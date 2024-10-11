<?php

    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;

    class ControllerCommonHeader extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
        
        public function index() {
            $this->data['url'] = URL_APP;
			
			$this->load_model('pages/system');
			$theme_id = $this->model_pages_system->get()['default_theme_id'];
			$themes = $this->model_pages_system->getThemes();
			
			foreach($themes as $theme) {
				if ($theme['id'] == $theme_id) {
					$this->data['path'] = $this->data['url'] . 'app/view/theme/' . $theme['path'];
				}
			}
            
            $this->data['menu'] = $this->load_controller('common/menu');
            
            $this->log->write(print_r($this->request->get, true));
            
            $action = explode('/', $this->request->get['route'])[1];
            
            if (!in_array($action, ['generate_captcha', 'getPhones', 'getMessage', 'getAll']) && !isset($this->request->session['history'])) {
                $this->load_controller('common/history', 'addHistory', ['page_view']);
                
                $this->session->data('history', true);
            }
            
            return $this->load_view('common/header', $this->secure->remove_tags($this->data, '<a><ul><li>'));
        }
    }
    