<?php

    namespace AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC;

    class Template {
        
        public function __construct() {
            header('content-type: text/html; charset=utf-8');
        }
        
        public function display($string) {
            echo $string;
        }
        
        public function render($file = '', $args = array(), $path) {
            if ($file != '') {
                $loader = new \Twig\Loader\FilesystemLoader(DIR_APP . 'view/theme/' . $path);
                $twig = new \Twig\Environment($loader, [
                    'cache'         => false
                ]);
                
                return html_entity_decode($twig->render($file . '.html', $args));
            }
        }
    }