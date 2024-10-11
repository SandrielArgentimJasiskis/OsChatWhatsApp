<?php

    namespace AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\App;

    class App {
		
		use \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Traits\Call;
        use \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Traits\Load;
		
        public function __construct() {
            $this->load_system('database/db');
            $this->load_system('data/log', 'error_' . date('Y-m-d') . '.log');
            $this->load_system('requests/mail');
            $this->load_system('requests/request');
            $this->load_system('requests/route');
            $this->load_system('requests/session');
            $this->load_system('requests/url');
            $this->load_system('requests/user');
            $this->load_system('mvc/template');
            $this->load_system('config/config');
            $this->load_system('config/form');
            $this->load_system('data/secure');
        }
    }
    