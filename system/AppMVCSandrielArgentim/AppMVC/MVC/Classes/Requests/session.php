<?php
    
    namespace AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Requests;
    
    /* Modifica o nome da sessão por motivos de segurança */
    $secure = new \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Data\Secure(array());
    $request = new \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Requests\Request(array());
    
    $session_name = $secure->hash(($request->server['REMOTE_ADDR'] ?? '') . $request->server['HTTP_USER_AGENT']);
    
    session_name($session_name);
    
    /* Aumenta o número de caracteres do ID da sessão por motivos de segurança */
    ini_set('session.sid_length', 128);
    
    /* Inicia o cookie com HTTPOnly habilitado por motivos de segurança */
    session_start([
        'cookie_lifetime' => 43200,
        'cookie_secure' => true,
        'cookie_httponly' => true
    ]);
    
    /* Regenera o ID da sessão por motivos de segurança */
    session_regenerate_id();
    
    class Session {
        
        public function data($session = '', $value = '') {
            if ($session != '') {
                $_SESSION[$session] = $value;
            }
        }
        
        public function destroy($session) {
            unset($_SESSION[$session]);
        }
        
    }
    