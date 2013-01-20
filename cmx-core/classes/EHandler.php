<?php

class CustomException extends Exception {
    protected $_Context = null;

    public function getContext() {
        return $this->_Context;
    }

    public function setContext($value) {
        $this->_Context = $value;
    }

    public function __construct($code, $message, $file, $line, $context) {
        parent::__construct($message, $code);

        $this->file = $file;
        $this->line = $line;
        $this->setContext($context);
    }
}

class EHandler {
    private function __construct() {
    }

    public static function error_handler($code, $message, $file, $line, $context) {

        if (error_reporting() === 0) {
            return;
        }

        throw new CustomException($code, $message, $file, $line, $context);
    }

    public static function exception_handler($e) {
        try {
            ob_start();
            //$lines = explode("\n", $output);
            //var_dump($lines);
            echo $e->getMessage();
            echo '<br/>';
            echo get_class($e);
            echo '<br/>';
            echo  $e->getFile();
            echo '<br/>';
            echo  $e->getLine();
            echo '<br/>';
            var_dump($e->getTrace());

            $dump = ob_get_clean();
            if (Config::$debug) {
                echo $dump;
            } else {
                echo file_get_contents(Config::$dataDir . '/custom/error.html');
            }
        } catch (Exception $e) {
            echo file_get_contents(Config::$dataDir . '/custom/error.html');
            echo 'ErrorHandler';
        }
        exit;
        /*echo '<h3>oops! something went seriously wrong.</h3><p>It would be pretty awesome if you could mail me (kobelipkens@gmail.com) the following info:</p>';
        var_dump($e);
        echo '<pre>' . $e->message . '<br/>';
        $file = basename($e->file);
        echo $file . ': ' . $e->line . '</pre>';
        exit;*/
    }
}
