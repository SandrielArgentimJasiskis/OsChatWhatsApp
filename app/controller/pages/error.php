<?php
    
    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
    
    class ControllerPagesError extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
        
        public function index() {
            if (!empty($this->request->session['error'])) {
                $this->data['error'] = $this->request->session['error'];
            }
            
            $this->template->display($this->load_view('pages/error', $this->secure->remove_tags($this->data, '', ['header', 'footer'])));
        }
    }
    