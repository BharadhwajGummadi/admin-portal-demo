<?php
namespace App\Error;

use Cake\Error\BaseErrorHandler;

class AppError extends BaseErrorHandler {
    
    public function _displayError($error, $debug) {
        return $error;
    }
    
    public function _displayException($exception) {
        return $exception;
    }
}