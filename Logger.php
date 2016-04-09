<?php

    /**
     * Created by PhpStorm.
     * User: godson
     * Date: 4/9/16
     * Time: 10:38
     */
    class Logger
    {
        private static $instance;

        private $fileHandler;

        private function __construct($filePath){
            $this->fileHandler = fopen($filePath,"a+");
        }

        public static function getInstance(){
            if(!self::$instance){
                self::$instance = new self("elevator.log");
            }
            return self::$instance;
        }

        public function write($string){
            fwrite($this->fileHandler,date("Y-m-d H:i:s")."\t$string".PHP_EOL);
        }

        public function __destruct()
        {
            fclose($this->fileHandler);
        }
    }