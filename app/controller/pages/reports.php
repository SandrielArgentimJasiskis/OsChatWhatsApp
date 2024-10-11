<?php
    
    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
    
    class ControllerPagesReports extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
        
        public function index() {
            $user_info = $this->load_model('pages/users', 'getUser', (array)$this->user->getId());
            
            $reports = $this->getList();
            
            if ($reports) {
                foreach($reports as $report) {
                    if ($this->secure->has_access('extensions/reports/' . $report['code'], 'access', $user_info)) {
                		if ($this->model_pages_extensions->getConfigStatusExtension($report['id'], 1) == '1') {
                            $this->data['reports'][] = array(
                                'id'        => $report['id'],
                                'title'     => $this->load_language('extensions/reports/' . $report['code'])['text_title'],
        						'edit'		=> $this->url->link('pages/reports/edit', '&extension_id=' . $report['id'])
                            );
                        }
                    }
                }
            }
        
            $this->template->display($this->load_view('pages/reports_list', $this->secure->remove_tags($this->data, '', ['header', 'footer'])));
        }
        
        public function edit() {
            $user_info = $this->load_model('pages/users', 'getUser', (array)$this->user->getId());
            
            $extension_id = $this->request->get['extension_id'] ?? 0;
            
            $this->load_model('pages/extensions');
            
            $extension = $this->model_pages_extensions->getExtensionById($extension_id);
            
            if ($extension['type'] == 'reports') {
                if (!$this->secure->has_access('extensions/reports/' . $extension['code'], 'access', $user_info)) {
                    $this->session->data('error', $this->data['text_error_access_access']);
    			
    			    $this->url->redirect('pages/error/index');
                }
                
                $status = $this->model_pages_extensions->getConfigStatusExtension($extension_id, 1);
                
                if ($status) {
                    $configs = $this->model_pages_extensions->getConfigExtension($extension_id, 1);
                    
                    $data_extension['id'] = $extension_id;
                    
					foreach($configs as $key => $config) {
						$data_extension[$config['config']] = $config['value'];
					}
					
					foreach($this->load_language('pages/extensions') as $key => $translation) {
						$data_extension[$key] = $translation;
					}
					foreach($this->load_language('extensions/reports/' . $extension['code']) as $key => $translation) {
						$data_extension[$key] = $translation;
					}
					
					if (empty($this->request->get['view'])) {
					    $this->data['export'] = $this->url->link('pages/reports/export', '&extension_id=' . $extension_id);
					} else {
					    $this->data['export'] = $this->url->link('pages/reports/export', '&extension_id=' . $extension_id . '&view=' . $this->secure->to_int($this->request->get['view']));
					}
					
                    $this->data['data'] = $this->load_controller('extensions/reports/' . $extension['code'], 'report', [$data_extension]);
                }
            }
            
            if (!empty($this->session->data['error'])) {
                $this->data['error'] = $this->request->session['error'];
            } else {
                $this->data['error'] = false;
            }
            
            $this->template->display($this->load_view('pages/reports_report', $this->secure->remove_tags($this->data, '<i><a><div><table><tr><th><td><thead><tbody>', ['header', 'footer'])));
        }
        
        public function export() {
            $user_info = $this->load_model('pages/users', 'getUser', (array)$this->user->getId());
            
            $extension_id = $this->request->get['extension_id'] ?? 0;
            
            $this->load_model('pages/extensions');
            
            $extension = $this->model_pages_extensions->getExtensionById($extension_id);
            
            if ($extension['type'] == 'reports') {
                if (!$this->secure->has_access('extensions/reports/' . $extension['code'], 'access', $user_info)) {
                    $this->session->data('error', $this->data['text_error_access_access']);
    			
    			    $this->url->redirect('pages/error/index');
                }
                
                $status = $this->model_pages_extensions->getConfigStatusExtension($extension_id, 1);
                
                if ($status) {
                    $configs = $this->model_pages_extensions->getConfigExtension($extension_id, 1);
                    
                    /* Adiciona ao histórico a modificação feita pelo usuário */
                    $this->load_controller('common/history', 'addHistory', ['export_report', json_encode($configs)]);
                    
                    $data_extension['id'] = $extension_id;
                    
					foreach($configs as $key => $config) {
						$data_extension[$config['config']] = $config['value'];
					}
					
					foreach($this->load_language('pages/extensions') as $key => $translation) {
						$data_extension[$key] = $translation;
					}
					foreach($this->load_language('extensions/' . $extension['type'] . '/' . $extension['code']) as $key => $translation) {
						$data_extension[$key] = $translation;
					}
					
					$this->data['export'] = $this->url->link('pages/reports/export', '&extension_id=' . $extension_id);
					
                    $this->load_controller('extensions/reports/' . $extension['code'], 'export', [$data_extension]);
                }
            }
        }
		
		public function getList() {
			$extensions_type = 'reports';
			
			return $this->load_model('pages/extensions', 'getExtensions', (array)$extensions_type);
		}
        
    }
    