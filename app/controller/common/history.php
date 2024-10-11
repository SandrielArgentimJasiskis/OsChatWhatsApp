<?php

    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;

    class ControllerCommonHistory extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {

    public function addHistory($activity, $previous_content = false, $user_id = false) {
            /* Adiciona ao histórico o acesso do usuário a página */
            $this->load_model('pages/users');
            
            $history_content = array(
                'route'  => $this->getCurrentRoute(),
                'current_url' => $this->url->get(),
                'reference_url' => $this->request->server['HTTP_REFERER'] ?? '',
                'ip' => $this->request->server['REMOTE_ADDR'] ?? '',
                'user_agent' => $this->request->server['HTTP_USER_AGENT']
            );
            
            if ($previous_content) {
                $history_content['previous_content'] = $previous_content;
            }
            
            if (!$user_id) {
                $user_id = $this->user->getId();
            }
            
            $history = array(
                'activity' => $activity,
                'content'  => $history_content,
                'user_id'  => $user_id
            );
            
            $this->model_pages_users->addActivity($history);
        }
    }
    