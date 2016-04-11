<?php

    /**
     * Logger class
     */
    class Logger
    {
        /**
         * Logger instance
         *
         * @var Logger
         */
        private static $instance;

        /**
         * Log file handler
         *
         * @var resource
         */
        private $fileHandler;

        /**
         * Logger constructor.
         *
         * @param $filePath
         */
        private function __construct($filePath){
            $this->fileHandler = fopen($filePath,"a+");
        }

        /**
         * Get logger instance
         *
         * @return Logger
         */
        public static function getInstance(){
            if(!self::$instance){
                self::$instance = new self("elevator.log");
            }
            return self::$instance;
        }

        /**
         * Write to log
         *
         * @param $string
         */
        public function write($string){
            fwrite($this->fileHandler,date("Y-m-d H:i:s")."\t$string".PHP_EOL);
        }

        /**
         * Destruct
         */
        public function __destruct()
        {
            fclose($this->fileHandler);
        }
    }