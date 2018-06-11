<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class RestApiController extends BaseController
{
    private $responseStatus = 'fail';
    private $responseMessage = '';
    private $responseCode = 400;

    /**
     * @return mixed
     */
    public function getResponseStatus()
    {
        return $this->responseStatus;
    }

    /**
     * @param mixed $responseStatus
     */
    public function setResponseStatus($responseStatus)
    {
        $this->responseStatus = $responseStatus;
    }

    /**
     * @return mixed
     */
    public function getResponseMessage()
    {
        return $this->responseMessage;
    }

    /**
     * @param mixed $responseMessage
     */
    public function setResponseMessage($responseMessage)
    {
        $this->responseMessage = $responseMessage;
    }

    /**
     * @return mixed
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * @param mixed $responseCode
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;
    }


    //
    protected function sendJsonResponse()
    {
        return response()->json([
            'status' => $this->getResponseStatus(),
            'message' => $this->getResponseMessage()
        ], $this->getResponseCode());
    }
}
