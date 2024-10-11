<?php

    namespace AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Requests;

    class Request {
        public function __construct() {
            $this->get = $_GET;
            $this->post = $_POST;
            $this->post_json = file_get_contents('php://input');
            $this->session = $_SESSION ?? array();
            $this->cookie = $_COOKIE ?? array();
            $this->server = $_SERVER;
            $this->files = $_FILES ?? array();
        }
    }
    