<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Transformers\ErrorTransformer;
use App\Transformers\SuccessTransformer;
use App\Transformers\UserTransformer;
use App\User;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use League\Fractal\Manager;
use Tymon\JWTAuth\JWTAuth;

class PasswordController extends ApiController
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * @var User
     */
    private $user;

    /**
     * @var JWTAuth
     */
    private $jwtauth;

    /**
     * Create a new password controller instance.
     *
     * @param User $user
     * @param JWTAuth $jwtauth
     * @param Manager $fractal
     */
    public function __construct(User $user, JWTAuth $jwtauth, Manager $fractal)
    {
        $this->middleware('guest');

        $this->user = $user;
        $this->jwtauth = $jwtauth;

        parent::__construct($fractal);
    }

    /**
     * Get the response for after the reset link has been successfully sent.
     *
     * @param $response
     * @return JsonResponse
     */
    protected function getSendResetLinkEmailSuccessResponse($response)
    {
        /*
        return new JsonResponse([
            'status' => trans($response),
        ], 202);
        */

        $successes = [
            'status' => trans($response)
        ];

        return $this->successPassword($successes, new SuccessTransformer);
    }

    /**
     * Get the response for after the reset link could not be sent.
     *
     * @param $response
     * @return JsonResponse
     */
    protected function getSendResetLinkEmailFailureResponse($response)
    {
        /*
        return new JsonResponse([
            'email' => [trans($response)],
        ], 422);
        */

        $errors = [
            'email' => trans($response)
        ];

        return $this->errorPassword($errors, new ErrorTransformer);
    }

    /**
     * Get the response for after a successful password reset.
     *
     * @param $response
     * @return JsonResponse
     */
    protected function getResetSuccessResponse($response)
    {
        /*
        return new JsonResponse([
            'status' => trans($response),
        ], 202);
        */

        $redirectTo = '/home';

        $newUser = Auth::user();

        return $this->successAuth($redirectTo, $this->jwtauth->fromUser($newUser), $newUser, new UserTransformer);
    }


    /**
     * Get the response for after a failing password reset.
     *
     * @param Request $request
     * @param $response
     * @return JsonResponse
     */
    protected function getResetFailureResponse(Request $request, $response)
    {
        /*
        return new JsonResponse([
            'email' => trans($response),
        ], 422);
        */

        $errors = [
            'email' => trans($response)
        ];

        return $this->errorPassword($errors, new ErrorTransformer);
    }
}
