<?php
    
    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
    
    class ControllerCommonMenu extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
        
        public function index() {
            if (!in_array($this->route->get(), ['pages/forgotten', 'pages/login'])) {
                $this->data['logged'] = true;
                
                $this->data['dashboard'] = $this->url->link('pages/dashboard/index');
                $this->data['messages'] = $this->url->link('pages/messages/index');
				$this->data['attendants'] = $this->url->link('pages/attendants/index');
                $this->data['users'] = $this->url->link('pages/users/index');
                $this->data['reports'] = $this->url->link('pages/reports/index');
                $this->data['extensions'] = $this->url->link('pages/extensions/index');
                $this->data['schedules'] = $this->url->link('pages/schedules/index');
                $this->data['settings'] = $this->url->link('pages/settings/index');
				$this->data['system'] = $this->url->link('pages/system/index');
                $this->data['logout'] = $this->url->link('pages/logout/index');
            }
            
            return $this->load_view('common/menu', $this->secure->remove_tags($this->data));
        }
    }