<?php

	namespace AppMVCSandrielArgentim\AppMVC\MVC\App;
	
	class ControllerExtensionsCaptchaBasic extends \AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\MVC\Controller {
	    
		public function index($data) {
			return $this->load_view('extensions/captcha/basic_form', $this->secure->remove_tags($data, '<i>'));
		}
		
		public function captcha() {
		    $this->data['captcha'] = $this->url->link('pages/login/generate_captcha');
		    
		    return $this->load_view('extensions/captcha/basic_captcha', $this->secure->remove_tags($this->data));
		}
		
		public function generate_captcha() {
		    $captcha = $this->secure->random(10);

			$this->session->data('captcha', $captcha);
			
			$this->load_model('pages/system');
			
			$theme_id = $this->model_pages_system->get()['default_theme_id'];
			$themes = $this->model_pages_system->getThemes();
			
			foreach($themes as $theme) {
				if ($theme['id'] == $theme_id) {
					$path = 'app/view/theme/' . $theme['path'] . '/';
				}
			}
			
			header("Cache-Control: no-store, no-cache, must-revalidate");
  

			$image = imagecreatefrompng($path . 'captcha.png');

			$captchaFont = imageloadfont($path . 'anonymous.gdf');
			
			$captchaColorR = $this->secure->randint(0, 255);
			$captchaColorG = $this->secure->randint(0, 255);
			$captchaColorB = $this->secure->randint(0, 255);
			
			$captchaColor = imagecolorallocate($image, $captchaColorR, $captchaColorG, $captchaColorB); //imagecolorallocate($image, 0, 0, 215);

			imagestring($image, $captchaFont, 0, 5, $captcha, $captchaColor);

			imagepng($image);

			imagedestroy($image);
		}
		
		public function validate() {
		    return $this->request->post['captcha'] == $this->request->session['captcha'];
		}
	}
	