<?php
    
    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
    
    class ControllerCommonFooter extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
        
        public function index() {
            $data = array();
            
            $data['styles'] = $this->load_controller('common/styles');
            
            return $this->load_view('common/footer', $this->secure->remove_tags($data, '<style>'));
        }
        
        public function __destruct() {
            if (isset($this->request->session['history'])) {
                $this->session->destroy('history');
            }
        }
    }