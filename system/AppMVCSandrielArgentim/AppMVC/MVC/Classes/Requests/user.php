<?php

    namespace AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Requests;

    class User {
		
		use \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Traits\Call;
		use \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Traits\Constructor {
			\AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Traits\Constructor::__construct as private __tConstruct;
		}
		use \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Traits\Load;
		
		public function getId() {
		    if (!empty($this->request->session['user_use_id'])) {
		        return $this->request->session['user_use_id'];
		    }
		    
		    return $this->request->session['user_id'] ?? 0;
		}
    }
