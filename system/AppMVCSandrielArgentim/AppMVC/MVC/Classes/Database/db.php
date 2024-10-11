<?php

    namespace AppMVCSandrielArgentim\AppMVC\MVC\System\Classes\Database;

    class DB {
        
        private $conn;
        
        public function __construct() {
            try {
                $this->conn = new \PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS);
                $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            } catch(PDOException $exception) {
                echo $exception . '<br>';
                die();
            }
        }
        
        public function query($query) {
            $statement = $this->conn->prepare($query);
            $statement->execute();
            
            $data['rows'] = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $data['num_rows'] = $statement->rowCount();
            
            return $data;
        }
        
        public function getLastId() {
            return $this->conn->lastInsertId();
        }
    }