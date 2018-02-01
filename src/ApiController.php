<?php

namespace Webfactor\Laravel\ApiController;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class ApiController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    private $requestBody;
    private $requestHeader;

    private $responseHttpHeader = [];
    private $responseStatusCode = 200;
    private $responseHeader = [];
    private $responseType = 'success';
    private $responseMessage = '';
    private $responsePayload;
    private $responseData;

    /**
     * ApiController constructor.
     * Automatically saves the requests body and header.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->requestBody = collect(json_decode($request->getContent(), true));

        $this->requestHeader = collect([]);

        foreach ($request->header() as $key => $value) {
            $this->requestHeader->put($key, $value[0]);
        }
    }

    protected function getRequestHeader($key = null)
    {
        if ($key) {
            return $this->requestHeader->get($key);
        }

        return $this->requestHeader->all();
    }

    protected function getRequestBody($key = null)
    {
        if ($key) {
            return $this->requestBody->get($key);
        }

        return $this->requestBody->all();
    }

    protected function setResponseStatusCode($statusCode)
    {
        $this->responseStatusCode = $statusCode;

        return $this;
    }

    protected function setResponseHttpHeader(array $header)
    {
        foreach ($header as $key => $value) {
            $this->responseHttpHeader[$key] = $value;
        }

        return $this;
    }

    protected function setResponseHeader($header)
    {
        $this->responseHeader = $header;

        return $this;
    }

    protected function setResponseType($responseType)
    {
        $this->responseType = $responseType;

        return $this;
    }

    protected function setResponseMessage($responseMessage)
    {
        $this->responseMessage = $responseMessage;

        return $this;
    }

    protected function setResponsePayload($data)
    {
        $this->responsePayload = $data;

        return $this;
    }

    protected function respondBadRequest($message = 'bad request')
    {
        return $this->setResponseStatusCode(400)
            ->setResponseMessage($message)
            ->respondWithError();
    }

    protected function respondUnauthorized()
    {
        return $this->setResponseStatusCode(401)
            ->setResponseMessage('Unauthorized')
            ->respondWithError();
    }

    protected function respondNotFound()
    {
        return $this->setResponseStatusCode(404)
            ->setResponseMessage('Not found')
            ->respondWithError();
    }

    protected function respondForbidden()
    {
        return $this->setResponseStatusCode(403)
            ->setResponseMessage('Forbidden')
            ->respondWithError();
    }

    protected function respondFailed()
    {
        return $this->setResponseStatusCode(422)
            ->setResponseMessage('No results')
            ->respondWithError();
    }

    protected function respondNoEntries()
    {
        return $this->respondFailed();
    }

    protected function respondInternalError()
    {
        return $this->setResponseStatusCode(500)
            ->setResponseMessage('Internal Error')
            ->respondWithError();
    }

    protected function respondWithError()
    {
        return $this->setResponseType('error')->respond();
    }

    protected function respondCreated()
    {
        return $this->setResponseStatusCode(201)
            ->respondWithSuccess();
    }

    protected function respondNotCreated($message = 'not created')
    {
        return $this->setResponseStatusCode(422)
            ->setResponseMessage($message)
            ->respondWithError();
    }

    protected function respondWithSuccess()
    {
        return $this->setResponseType('success')->respond();
    }

    protected function respond()
    {
        $this->prepareRespondData();

        return response()->json($this->responseData, $this->responseStatusCode, $this->responseHttpHeader);
    }

    private function prepareRespondData()
    {
        $this->responseData = [
            'header' => [
                'type' => $this->responseType,
                'message' => $this->responseMessage,
                'code' => $this->responseStatusCode,
            ],
        ];

        if ($this->responseHeader) {
            foreach ($this->responseHeader as $key => $item) {
                $this->responseData['header'][$key] = $item;
            }
        }

        if ($this->responsePayload) {
            foreach ($this->responsePayload as $key => $item) {
                $this->responseData[$key] = $item;
            }
        }
    }
}
