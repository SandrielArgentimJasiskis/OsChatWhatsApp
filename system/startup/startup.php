<?php
    
    require_once(DIR_SYSTEM . 'vendor/autoload.php');
    
    $app = new \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\App\App();
	$app->init();
    
	if (empty($app->request->get['route'])) {
		if (!$app->secure->verify()) {
			$app->url->redirect('pages/login/index');
		} else {
			$app->url->redirect('pages/dashboard/index');
		}
	}
    if (substr_count($app->request->get['route'], '/') != 2) {
        if (!$app->secure->verify()) {
			$app->url->redirect('pages/login/index');
		} else {
			$app->url->redirect('pages/dashboard/index');
		}
    }
    
	$route = $app->route->get();
	
    if ($route != 'pages/error') {
        $app->session->destroy('error');
    }
    
    if (!$app->secure->verify()) {
        if (!in_array($route, ['pages/login', 'pages/forgotten', 'webhook/whatsapp', 'pages/app/view/common.js', 'pages/app/view/common.css'])) {
            $app->log->write('Rota desejada ' . $route . '. Usuário não logado. Redirecionando para a página de login.');
            
            $app->load_controller('common/history', 'addHistory', ['page_view']);
            
            $app->url->redirect('pages/login/index');
        }
    }
    
	if ($app->user->getId() != 0) {
	    $translations = $app->load_language('common/error');
	    
		$user_info = $app->load_model('pages/users', 'getUser', (array)$app->user->getId());
		
		if (substr($route, 0, 11) != 'extensions/') {
    		if (!$app->secure->has_access($route, 'access', $user_info)) {
    			$app->session->data('error', $translations['text_error_access_access']);
    			
    			$app->url->redirect('pages/error/index');
    		}
    		if (!empty($app->request->post) && !$app->secure->has_access($route, 'modify', $user_info)) {
    			$app->session->data('error', $translations['text_error_access_modify']);
    			
    			$app->url->redirect('pages/error/index');
    		}
		}
	}
    
	$action = explode('/', $app->request->get['route'])[1];
	
	$route = ($route) ? $route : 'pages/dashboard';
	
	$app->setCurrentRoute($route);
	
    $app->load_controller($route, $action);
    