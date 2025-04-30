<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiStatusResponses
{
    /**
     * Success Response.
     *
     * @param  array  $data
     * @param  int  $statusCode
     * @return JsonResponse
     */
    public function successResponse(array $data = [], string $message = '', int $statusCode = Response::HTTP_OK): JsonResponse
    {
        $data[API_RESPONSE_STATUS_FIELD] = API_RESPONSE_STATUS_SUCCESS;
        $data[API_RESPONSE_MESSAGE_FIELD] = $message ?? Response::$statusTexts[$statusCode];
        return new JsonResponse($data, $statusCode);
    }

    /**
     * Error Response.
     *
     * @param  array  $data
     * @param  string  $message
     * @param  int  $statusCode
     * @return JsonResponse
     */
    public function errorResponse(array $data = [], string $message = '', int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        $data[API_RESPONSE_STATUS_FIELD] = API_RESPONSE_STATUS_ERROR;
        $data[API_RESPONSE_MESSAGE_FIELD] = $message ?? Response::$statusTexts[$statusCode];
        return new JsonResponse($data, $statusCode);
    }

    /**
     * Response with status code 200.
     *
     * @param  array  $data
     * @return JsonResponse
     */
    public function okResponse(array $data = [], string $message = ''): JsonResponse
    {
        return $this->successResponse($data);
    }

    /**
     * Response with status code 201.
     *
     * @param  array  $data
     * @return JsonResponse
     */
    public function createdResponse(array $data = [], string $message = ''): JsonResponse
    {
        return $this->successResponse($data, $message, Response::HTTP_CREATED);
    }

    /**
     * Response with status code 204.
     *
     * @return JsonResponse
     */
    public function noContentResponse(): JsonResponse
    {
        return new JsonResponse([], $Response::HTTP_NO_CONTENT);
    }

    /**
     * Response with status code 400.
     *
     * @param  array  $data
     * @param  string  $message
     * @return JsonResponse
     */
    public function badRequestResponse(array $data = [], string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, $message, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Response with status code 401.
     *
     * @param  array  $data
     * @param  string  $message
     * @return JsonResponse
     */
    public function unauthorizedResponse(array $data = [], string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, $message, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Response with status code 403.
     *
     * @param  array  $data
     * @param  string  $message
     * @return JsonResponse
     */
    public function forbiddenResponse(array $data = [], string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, $message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Response with status code 404.
     *
     * @param  array  $data
     * @param  string  $message
     * @return JsonResponse
     */
    public function notFoundResponse(array $data = [], string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, $message, Response::HTTP_NOT_FOUND);
    }

    /**
     * Response with status code 409.
     *
     * @param  array  $data
     * @param  string  $message
     * @return JsonResponse
     */
    public function conflictResponse(array $data = [], string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, $message, Response::HTTP_CONFLICT);
    }

    /**
     * Response with status code 422.
     *
     * @param  array  $data
     * @param  string  $message
     * @return JsonResponse
     */
    public function unprocessableResponse(array $data = [], string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, $message, Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
