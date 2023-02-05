<?php

namespace App\Exceptions;

use App\Repositories\LogNotificationRepository;
use App\Traits\Responses;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use responses;
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        //http exception
        if ($exception instanceof HttpException){
            $code = $exception->getStatusCode();
            $message = Response::$statusTexts[$code];
            return $this->response($code, $message, '');
        }

        //Model not found exception
        if ($exception instanceof ModelNotFoundException){
            $model = strtolower(class_basename($exception->getModel()));
            return $this->response(ResponseAlias::HTTP_NOT_FOUND, "{$model} does not exist with the given id", "");
        }

        //authorization exception
        if($exception instanceof AuthorizationException){
            return $this->response(ResponseAlias::HTTP_FORBIDDEN, $exception->getMessage(),'');
        }

        //authentication exception
        if($exception instanceof AuthenticationException){
            return $this->response(ResponseAlias::HTTP_UNAUTHORIZED, $exception->getMessage(),'');
        }

        //Validation exception
        if($exception instanceof ValidationException){
//            $errors = $exception->validator->errors()->getMessages();
            $errors = $exception->validator->messages()->all();
            return $this->response(ResponseAlias::HTTP_UNPROCESSABLE_ENTITY, $errors,'');
        }

//        $logNotificationRepository = new LogNotificationRepository();
//        $res = ['Error' => ['message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()], 'userId'=>Auth::check()?Auth::user()->id:'', 'address' => config('app.url')];
//        $parameters = ['accessToken' => '', 'channel' => ['slack'], 'type' => 'error', 'name' => 'Server Error', 'message' => json_encode($res)];
//        $logNotificationRepository->sendLog($parameters);

        if (env('APP_DEBUG', false)){
//            return self::response(ResponseAlias::HTTP_BAD_REQUEST, 'Something went wrong.', null);
            return parent::render($request, $exception);
        }

        //unexpected error
        $res = ['Error' => ['message' => $exception->getMessage(), 'file' => $exception->getFile(), 'line' => $exception->getLine()], 'userId'=>Auth::check()?Auth::user()->id:'', 'address' => config('app.url')];
        return $this->response(ResponseAlias::HTTP_INTERNAL_SERVER_ERROR, 'Unexpected error. Try again', $res);
    }
}
