<?php
    
    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
    
    class ControllerCommonStyles extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
        
        public function index() {
            $data = array();
            
            $data['styles'] = array();
            
            $this->load_model('common/theme');
            $this->load_model('pages/extensions');
            $this->load_model('pages/system');
            
            $system = $this->model_pages_system->get();
            
            $theme_id = $system['default_theme_id'];
            
            $theme_code = $this->model_common_theme->getThemeByID($theme_id)['code'];
            
            $configs = $this->model_pages_extensions->getConfigExtensionByCode($theme_code, 1);
            
            foreach($configs as $key => $config) {
                if ($config['config'] != 'status') {
                    $data['styles'][] = $config;
                }
			}
			
			$data['fonts'] = $this->load_model('common/theme', 'getFonts');
            
            return $this->load_view('common/styles', $this->secure->remove_tags($data));
        }
    }