<?php
    
    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
    
    class ControllerPagesdashboard extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
        
        public function index() {
            $this->load_model('pages/messages');
            $this->load_model('pages/extensions');
			$this->load_model('pages/system');
			
			$this->data['url'] = URL_APP;
			
			$theme_id = $this->model_pages_system->get()['default_theme_id'];
			$themes = $this->model_pages_system->getThemes();
			
			foreach($themes as $theme) {
				if ($theme['id'] == $theme_id) {
					$this->data['path'] = $this->data['url'] . 'app/view/theme/' . $theme['path'];
				}
			}
            
            $range = $this->request->get['range'] ?? 'month';
            $range = ($this->secure->is_date_range($range)) ? $range : 'month';
            
            $user_info = $this->load_model('pages/users', 'getUser', (array)$this->user->getId());
            
            $extensions = $this->model_pages_extensions->getExtensions('dashboard');
            
            $ignore = ['header', 'footer'];
            
            $this->data['extensions'] = array();
            foreach($extensions as $extension) {
                if ($this->secure->has_access('extensions/dashboard/' . $extension['code'], 'access', $user_info)) {
                    $is_to_all_users = $this->config->get_all('extensions/dashboard/' . $extension['code'])['to_all_users'];
                    
                    if ($this->model_pages_extensions->getConfigStatusExtension($extension['id'], $is_to_all_users) == '1') {
    					$data_extension = array();
    					
    					$data_extension['range'] = $range;
    					
    					$configs = $this->model_pages_extensions->getConfigExtension($extension['id'], $is_to_all_users);
    					
    					foreach($configs as $key => $config) {
    						$data_extension[$config['config']] = $config['value'];
    					}
    					
    					foreach($this->load_language('pages/extensions') as $key => $translation) {
    						$data_extension[$key] = $translation;
    					}
    					foreach($this->load_language('extensions/' . $extension['type'] . '/' . $extension['code']) as $key => $translation) {
    						$data_extension[$key] = $translation;
    					}
    					
    					$order_extension = $data_extension['order'] ?? 0;
    					
    					$ignore[] = 'extensions/dashboard/' . $extension['code'];
    					
                        $this->data['extensions'][$this->secure->to_int($order_extension)][] = $this->load_controller('extensions/dashboard/' . $extension['code'], 'dashboard', [$data_extension]);
                    }
                }
            }
            
            ksort($this->data['extensions']);
            
            $this->data['range'] = $range;
            
            $this->data['link_yesterday'] = $this->url->link('pages/dashboard/index', '&range=yesterday');
            $this->data['link_day'] = $this->url->link('pages/dashboard/index', '&range=day');
            $this->data['link_week'] = $this->url->link('pages/dashboard/index', '&range=week');
            $this->data['link_month'] = $this->url->link('pages/dashboard/index', '&range=month');
            $this->data['link_year'] = $this->url->link('pages/dashboard/index', '&range=year');
        
            $this->data['url'] = URL_APP;
			
            $this->template->display($this->load_view('pages/dashboard', $this->data));
        }
        
        public function getMessagesTotal() {
            $this->load_model('pages/messages');
            $this->data['message_status']['data'][] = [0, $this->secure->to_int($this->model_pages_messages->getTotalMessagesByStatus('sent'))];
            $this->data['message_status']['data'][] = [1, $this->secure->to_int($this->model_pages_messages->getTotalMessagesByStatus('read'))];
            
            echo json_encode($this->data);
        }
    }
    