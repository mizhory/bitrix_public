<?php


namespace Vigr\Budget\Traits;
use \Exception;

trait ErrorTrait
{
    private $arErrors = [];

    public function pushInErrors($errorType,$errorInfo){
        if(!array_key_exists($errorType,$this->arErrors)){
            $this->arErrors[$errorType] = [];
        }
        $this->arErrors[$errorType][] = $errorInfo;
    }

    public function checkErrors(){
        return empty($this->arErrors) ? false : $this->arErrors;
    }
}