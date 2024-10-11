<?php

    namespace AppMVCSandrielArgentim\AppMVC\MVC\App;

    class ControllerPagesForgotten extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
        
        public function index() {
            if (!empty($this->request->post) && $this->validate()) {
                $this->load_model('pages/users');
                $user = $this->model_pages_users->getUserByEmail($this->request->post['email']);
                
                if ($user) {
                    $this->load_model('pages/system');
                    $code = $this->secure->random(256);
                    $this->session->data('code', $code);
                    
                    $system = $this->model_pages_system->get();
                    
                    $this->mail->setCredentials([
                        'host'          => $system['mail_host'],
                        'username'      => $system['mail_username'],
                        'password'      => $system['mail_password'],
                        'encryption'    => $system['mail_encryption'],
                        'port'          => $system['mail_port'],
                    ]);
                    
                    $this->mail->setFrom($system['mail_username']);
                    $this->mail->addAddress($this->secure->clear($this->request->post['email']));
                    $this->mail->setSubject($this->data['text_mail_subject']);
                    $this->mail->setBody([
                        'body'      => sprintf($this->data['text_mail_body'], $this->data['text_mail_subject'], $this->url->link('pages/forgotten/index', $code), $code),
                        'alt_body'  => sprintf($this->data['text_mail_alt_body'], $code),
                    ]);
                    $this->mail->send();
                    
                    $this->data['msg'] = $this->data['text_msg_success_sent'];
                    
                    /*$this->session->data('user_id', $login);
                    
                    $this->load_controller('common/history', 'addHistory', ['login', array(), $login]);
                    
                    if ($this->request->session['redirect'] == '') {
                        $this->request->session['redirect'] = 'pages/dashboard/index';
                    }
                    
                    $this->url->redirect($this->request->session['redirect']);*/
                }
            }
            
            if ($this->getError()) {
                $this->data['error'] = $this->getError();
            }
            
            $this->data['action'] = $this->url->link('pages/forgotten/index');
            $this->data['back'] = $this->url->link('pages/login/index');
            
            $this->data['email'] = $this->request->post['email'] ?? '';
             
            /* Gera um token aleatório anti CSRF */
            $this->data['token_name'] = '_' . $this->secure->random(64);
            $this->data['token_value'] = $this->secure->random(64);
            $this->session->data('token', [
                'name'  => $this->data['token_name'], 
                'value' => $this->data['token_value']
            ]);
            
            $this->template->display($this->load_view('pages/forgotten', $this->secure->remove_tags($this->data, '', ['header', 'footer', 'captcha'])));
        }
        
        private function validate() {
            if (!isset($this->request->post[$this->request->session['token']['name']]) || !isset($this->request->post['email'])) {
                $this->secure->access_denied();
            }
            
            if ($this->request->post[$this->request->session['token']['name']] !== $this->request->session['token']['value']) {
                $this->secure->access_denied();
            }
            
            if (!isset($this->request->post['email']) || !$this->secure->is_user($this->request->post['email'])) {
                $this->setError($this->data['text_error_login']);
                return false;
            }
            
            return true;
        }
    }