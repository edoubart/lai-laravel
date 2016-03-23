<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests;
use App\Transformers\ErrorTransformer;
use App\Transformers\UserTransformer;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use JWTAuthException;
use League\Fractal\Manager;
use Tymon\JWTAuth\JWTAuth;
use Validator;

class AuthController extends ApiController
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * @var User
     */
    private $user;

    /**
     * @var JWTAuth
     */
    private $jwtauth;

    /**
     * Create a new authentication controller instance.
     *
     * @param User $user
     * @param JWTAuth $jwtauth
     * @param Manager $fractal
     */
    public function __construct(User $user, JWTAuth $jwtauth, Manager $fractal)
    {
        $this->middleware($this->guestMiddleware(), ['except' => 'logout']);

        $this->user = $user;
        $this->jwtauth = $jwtauth;

        parent::__construct($fractal);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

    /**
     * Handle a login request to the application.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        $throttles = $this->isUsingThrottlesLoginsTrait();

        if ($throttles && $lockedOut = $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $credentials = $this->getCredentials($request);

        if (Auth::guard($this->getGuard())->attempt($credentials, $request->has('remember'))) {
            $token = null;

            $token = $this->jwtauth->attempt($credentials, ['remember' => $request->has('remember')]);

            return $this->handleUserWasAuthenticated($request, $throttles, $token);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        if ($throttles && !$lockedOut) {
            $this->incrementLoginAttempts($request);
        }

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param Request $request
     * @param $throttles
     * @param $token
     * @return JsonResponse
     */
    protected function handleUserWasAuthenticated(Request $request, $throttles, $token)
    {
        if ($throttles) {
            $this->clearLoginAttempts($request);
        }

        if (method_exists($this, 'authenticated')) {
            return $this->authenticated($request, Auth::guard($this->getGuard())->user());
        }

        $redirectTo = $this->redirectPath();

        $user = Auth::user();

        return $this->successAuth($redirectTo, $token, $user, new UserTransformer);
    }

    /**
     * Get the failed login response instance.
     *
     * @return JsonResponse
     */
    protected function sendFailedLoginResponse()
    {
        /*
        return new JsonResponse([
            $this->loginUsername() => $this->getFailedLoginMessage(),
        ], 422);
        */

        $errors = [
            $this->loginUsername() => $this->getFailedLoginMessage()
        ];

        return $this->errorAuth($errors, new ErrorTransformer);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {

            /*
            $this->throwValidationException(
                $request, $validator
            );
            */

            $errors = $this->formatValidationErrors($validator);

            return $this->errorAuth($errors, new ErrorTransformer);
        }

        Auth::guard($this->getGuard())->login($this->create($request->all()));

        $redirectTo = $this->redirectPath();

        $newUser = Auth::user();

        return $this->successAuth($redirectTo, $this->jwtauth->fromUser($newUser), $newUser, new UserTransformer);
    }

    /**
     * Log the user out of the application.
     *
     * @return JsonResponse
     */
    public function logout()
    {
        Auth::guard($this->getGuard())->logout();

        $redirectTo = property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/';

        return $this->successLogout($redirectTo);
    }
}
