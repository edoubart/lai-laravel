<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Response;

class ApiController extends Controller
{
    protected $statusCode = 200;

    // Customs Code
    const CODE_CONTINUE = '100 Continue';
    const CODE_SWITCHING_PROTOCOLS = '101 Switching Protocols';
    const CODE_OK = '200 OK';
    const CODE_CREATED = '201 Created';
    const CODE_ACCEPTED = '202 Accepted';
    const CODE_NON_AUTHORITATIVE_INFORMATION = '203 Non-Authoritative Information';
    const CODE_NO_CONTENT = '204 No Content';
    const CODE_RESET_CONTENT = '205 Reset Content';
    const CODE_PARTIAL_CONTENT = '206 Partial Content';
    const CODE_MULTIPLE_CHOICES = '300 Multiple Choice';
    const CODE_MOVE_PERMANENTLY = '301 Moved Permanently';
    const CODE_FOUND = '302 Found';
    const CODE_SEE_OTHER = '303 See Other';
    const CODE_NOT_MODIFIED = '304 Not Modified';
    const CODE_USE_PROXY = '305 Use Proxy';
    const CODE_TEMPORARY_REDIRECT = '307 Temporary Redirect';
    const CODE_BAD_REQUEST = '400 Bad Request';
    const CODE_UNAUTHORIZED = '401 Unauthorized';
    const CODE_PAYMENT_REQUIRED = '402 Payment Required';
    const CODE_FORBIDDEN = '403 Forbidden';
    const CODE_NOT_FOUND = '404 Not Found';
    const CODE_METHOD_NOT_ALLOWED = '405 Method Not Allowed';
    const CODE_NOT_ACCEPTABLE = '406 Not Acceptable';
    const CODE_PROXY_AUTHENTICATION_REQUIRED = '407 Proxy Authentication Required';
    const CODE_REQUEST_TIME_OUT = '408 Request Time-out';
    const CODE_CONFLICT = '409 Conflict';
    const CODE_GONE = '410 Gone';
    const CODE_LENGTH_REQUIRED = '411 Length Required';
    const CODE_PRECONDITION_FAILED = '412 Precondition Failed';
    const CODE_REQUEST_ENTITY_TOO_LARGE = '413 Request Entity Too Large';
    const CODE_REQUEST_URI_TOO_LARGE = '414 Request-URI Too Large';
    const CODE_UNSUPPORTED_MEDIA_TYPE = '415 Unsupported Media Type';
    const CODE_REQUESTED_RANGE_NOT_SATISFIABLE = '416 Requested range not satisfiable';
    const CODE_EXPECTATION_FAILED = '417 Expectation Failed';
    const CODE_UNPROCESSABLE_ENTITY = '422 Unprocessable Entity';
    const CODE_INTERNAL_SERVER_ERROR = '500 Internal Server Error';
    const CODE_NOT_IMPLEMENTED = '501 Not Implemented';
    const CODE_BAD_GATEWAY = '502 Bad Gateway';
    const CODE_SERVICE_UNAVAILABLE = '503 Service Unavailable';
    const CODE_GATEWAY_TIME_OUT = '504 Gateway Time-out';
    const CODE_HTTP_VERSION_NOT_SUPPORTED = '505 HTTP Version not supported';

    /**
     * @param Manager $fractal
     */
    public function __construct(Manager $fractal)
    {
        $this->fractal = $fractal;

        // Are we going to try and include embedded data?
        if (Input::get('include')) {
            $this->fractal->parseIncludes(Input::get('include'));
        }
    }

    /**
     * Getter for statusCode
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Setter for statusCode
     *
     * @param int $statusCode Value to set
     *
     * @return self
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Respond with a item
     *
     * @param $item
     * @param $callback
     * @return mixed
     */
    protected function respondWithItem($item, $callback)
    {
        $resource = new Item($item, $callback);

        $rootScope = $this->fractal->createData($resource);

        return $this->respondWithArray($rootScope->toArray());
    }

    /**
     * Respond with a collection
     *
     * @param $collection
     * @param $callback
     * @return mixed
     */
    protected function respondWithCollection($collection, $callback)
    {
        $resource = new Collection($collection, $callback);

        $rootScope = $this->fractal->createData($resource);

        return $this->respondWithArray($rootScope->toArray());
    }

    /**
     * Respond with an array
     *
     * @param array $array
     * @param array $headers
     * @return mixed
     */
    protected function respondWithArray(array $array, array $headers = [])
    {
        $contentType = 'application/json';

        $content = json_encode($array);

        $response = Response::make($content, $this->statusCode, $headers);

        $response->header('Content-Type', $contentType);

        return $response;
    }

    /**
     * Respond with an error
     *
     * @param $message
     * @param $errorCode
     * @return mixed
     */
    protected function respondWithError($message, $errorCode)
    {
        if ($this->statusCode === 200) {
            trigger_error(
                "You better have a really good reason for erroring on a 200...",
                E_USER_WARNING
            );
        }

        return $this->respondWithArray([
            'error' => [
                'code' => $errorCode,
                'http_code' => $this->statusCode,
                'message' => $message,
            ]
        ]);
    }

    /**
     * Respond with success
     *
     * @param $message
     * @param $successCode
     * @return mixed
     */
    protected function respondWithSuccess($message, $successCode, $item = null, $callback = null)
    {
        if (!$item) {
            return $this->respondWithArray([
                'success' => [
                    'code' => $successCode,
                    'http_code' => $this->statusCode,
                    'message' => $message,
                ]
            ]);
        } else {
            $resource = new Item($item, $callback);

            $rootScope = $this->fractal->createData($resource);

            $success = [
                'code' => $successCode,
                'http_code' => $this->statusCode,
                'message' => $message
            ];

            $response['success'] = $success + $rootScope->toArray();

            return $this->respondWithArray($response);
        }
    }

    /**
     * Generates a Response with a 400 HTTP header and a given message.
     *
     * @return Response
     */
    public function errorBadRequest($message = 'Bad Request')
    {
        return $this->setStatusCode(400)
            ->respondWithError($message, self::CODE_BAD_REQUEST);
    }

    /**
     * Generates a Response with a 401 HTTP header and a given message.
     *
     * @return Response
     */
    public function errorUnauthorized($message = 'Unauthorized')
    {
        return $this->setStatusCode(401)
            ->respondWithError($message, self::CODE_UNAUTHORIZED);
    }

    /**
     * Generates a Response with a 403 HTTP header and a given message.
     *
     * @return Response
     */
    public function errorForbidden($message = 'Forbidden')
    {
        return $this->setStatusCode(403)
            ->respondWithError($message, self::CODE_FORBIDDEN);
    }

    /**
     * Generates a Response with a 404 HTTP header and a given message.
     *
     * @return Response
     */
    /*
    public function errorResourceNotFound($message = 'Resource Not Found')
    {
        return $this->setStatusCode(404)
            ->respondWithError($message, self::CODE_NOT_FOUND);
    }
    */

    /**
     * Generates a Response with a 422 HTTP header and a given message.
     *
     * @return Response
     */
    public function errorUnprocessableEntity($message = 'Unprocessable Entity')
    {
        return $this->setStatusCode(422)
            ->respondWithError($message, self::CODE_UNPROCESSABLE_ENTITY);
    }

    /**
     * Generates a Response with a 500 HTTP header and a given message.
     *
     * @return Response
     */
    public function errorInternalServerError($message = 'Internal Server Error')
    {
        return $this->setStatusCode(500)
            ->respondWithError($message, self::CODE_INTERNAL_SERVER_ERROR);
    }

    /**
     * Generates a Response with a 201 HTTP header and a given message.
     *
     * @return Response
     */
    public function successCreated($message = 'The resource has been created with success!', $item = null, $callback = null)
    {
        return $this->setStatusCode(201)
            ->respondWithSuccess($message, self::CODE_CREATED, $item, $callback);
    }

    /**
     * Generates a Response with a 201 HTTP header and a given message.
     *
     * @return Response
     */
    public function successUpdated($message = 'The resource has been updated with success!', $item = null, $callback = null)
    {
        return $this->setStatusCode(202)
            ->respondWithSuccess($message, self::CODE_ACCEPTED, $item, $callback);
    }

    /**
     * Generates a Response with a 201 HTTP header and a given message.
     *
     * @return Response
     */
    public function successDeleted($message = 'The resource has been deleted with success!')
    {
        return $this->setStatusCode(202)
            ->respondWithSuccess($message, self::CODE_ACCEPTED);
    }

    /**
     * Generates a Response with a 201 HTTP header and a given message for a successful Auth.
     *
     * @param $redirectTo
     * @param $token
     * @param $user
     * @param $callback
     * @return Response
     */
    public function successAuth($redirectTo, $token, $user, $callback)
    {
        $this->setStatusCode(202);

        $successCode = self::CODE_ACCEPTED;

        $success = [
            'code' => $successCode,
            'http_code' => $this->statusCode,
            'redirectTo' => $redirectTo,
            'token' => $token
        ];

        $resource = new Item($user, $callback);

        $rootScope = $this->fractal->createData($resource);

        $response['success'] = $success + $rootScope->toArray();

        return $this->respondWithArray($response);
    }

    /**
     * Generates a Response with a 422 HTTP header and a given message for an Auth with errors.
     *
     * @param $errors
     * @param $callback
     * @return mixed
     */
    public function errorAuth($errors, $callback)
    {
        $this->setStatusCode(422);

        $errorCode = self::CODE_UNPROCESSABLE_ENTITY;

        $error = [
            'code' => $errorCode,
            'http_code' => $this->statusCode
        ];

        $resource = new Item($errors, $callback);

        $rootScope = $this->fractal->createData($resource);

        $response['error'] = $error + $rootScope->toArray();

        return $this->respondWithArray($response);
    }

    /**
     * Generates a Response with a 202 HTTP header and a given message for a successful logout.
     *
     * @param $redirectTo
     * @return Response
     */
    public function successLogout($redirectTo)
    {
        $this->setStatusCode(202);

        $successCode = self::CODE_ACCEPTED;

        $success = [
            'code' => $successCode,
            'http_code' => $this->statusCode,
            'redirectTo' => $redirectTo
        ];

        $response['success'] = $success;

        return $this->respondWithArray($response);
    }

    /**
     * Generates a Response with a 202 HTTP header and a given message for a successful Password.
     *
     * @param $successes
     * @param $callback
     * @return mixed
     */
    public function successPassword($successes, $callback)
    {
        $this->setStatusCode(202);

        $successCode = self::CODE_ACCEPTED;

        $success = [
            'code' => $successCode,
            'http_code' => $this->statusCode
        ];

        $resource = new Item($successes, $callback);

        $rootScope = $this->fractal->createData($resource);

        $response['success'] = $success + $rootScope->toArray();

        return $this->respondWithArray($response);
    }

    /**
     * Generates a Response with a 422 HTTP header and a given message for a Password with errors.
     *
     * @param $errors
     * @param $callback
     * @return mixed
     */
    public function errorPassword($errors, $callback)
    {
        $this->setStatusCode(422);

        $errorCode = self::CODE_UNPROCESSABLE_ENTITY;

        $error = [
            'code' => $errorCode,
            'http_code' => $this->statusCode
        ];

        $resource = new Item($errors, $callback);

        $rootScope = $this->fractal->createData($resource);

        $response['error'] = $error + $rootScope->toArray();

        return $this->respondWithArray($response);
    }
}
