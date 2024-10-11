<?php
    
    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
    
    class ControllerPagesLogOut extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
        
        public function index() {
            $this->load_controller('common/history', 'addHistory', ['logout']);
            
            $this->secure->logout();
            
            $this->session->data('msg', $this->data['text_logout']);
            $this->session->data('redirect', 'dashboard');
            
            $this->url->redirect('pages/login/index');
        }
    }
    