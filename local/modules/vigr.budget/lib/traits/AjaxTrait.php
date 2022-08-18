<?php


namespace Vigr\Budget\Traits;


trait AjaxTrait
{
    protected $status;

    protected $response;

    public function sendRequest()
    {
        echo json_encode([
            'status'=>$this->status,
            'response'=>$this->response
        ]);
    }
}