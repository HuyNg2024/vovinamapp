<?php

namespace App\Exceptions;

use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    //xử lý chi tiết các trường hợp token
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof AuthenticationException) {
            return $this->handleAuthenticationException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Handle authentication exceptions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function handleAuthenticationException($request, AuthenticationException $exception)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            // If we can authenticate the user but still get an AuthenticationException,
            // it means their token is no longer the active one
            if ($user) {
                return response()->json([
                    'error' => 'Tài khoản đang được đăng nhập từ thiết bị khác'
                ], 401);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'error' => 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại'
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'error' => 'Tài khoản đang được đăng nhập từ thiết bị khác. Vui lòng đăng nhập lại'
            ], 401);
        } catch (JWTException $e) {
            // Unable to parse the token
        }

        // If we reach here, it means we couldn't parse the token or authenticate the user
        return response()->json([
            //'error' => 'Unauthenticated'
            'error' => 'Vui lòng truyền thêm token'

        ], 401);
    }
}
