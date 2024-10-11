<?php

    namespace AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Requests;
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    class Mail {
        private $mail;
        
        public function __construct() {
            $this->mail = new PHPMailer(true);
            
            $this->mail->isSMTP();          
        }
        
        public function addAddress($email, $name = false) {
            if (!$name) {
                $this->mail->addAddress($email);
            } else {
                $this->mail->addAddress($email, $name);
            }
        }
        
        public function send() {
            try {
                $this->mail->send();
                
                return array(
                    'success' => true
                );
            } catch (Exception $e) {
                return array(
                    'success'   => false,
                    'error'     => $this->mail->ErrorInfo
                );
            }
        }
        
        public function setBody($data) {
            $this->mail->isHTML(true);       
            $this->mail->Body    = $data['body'];
            if (isset($data['alt_body'])) {
                $this->mail->AltBody = $data['alt_body'];
            }
        }
        
        public function setCredentials($credentials) {
            $this->mail->Host       = $credentials['host'];
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = $credentials['username'];
            $this->mail->Password   = $credentials['password'];
            $this->mail->SMTPSecure = $credentials['encryption'];
            $this->mail->Port       = $credentials['port'];
        }
        
        public function setFrom($email, $name = '') {
            $this->mail->setFrom($email, $name);
        }
        
        public function setSubject($subject) {
            $this->mail->Subject = $subject;
        }
    }
    