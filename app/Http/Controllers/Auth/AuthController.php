<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Socialite;
use Validator;

class AuthController extends Controller
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
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Implemented providers for social authentication.
     *
     * @var array
     */
    protected $providers = ['facebook', 'google'];


    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
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
     * Redirect the user to the provider authentication page.
     *
     * @param $provider
     * @return Response
     */
    public function redirectToProvider($provider)
    {
        if (!$provider || !in_array($provider, $this->providers)) {
            exit();
        }

        return Socialite::driver($provider)->scopes(['email'])->redirect();
    }

    /**
     * Obtain the user information from the provider.
     *
     * @param $provider
     * @return Response
     * @throws \Illuminate\Foundation\Validation\ValidationException
     */
    public function handleProviderCallback($provider)
    {
        if (!$provider || !in_array($provider, $this->providers)) {
            exit();
        }

        $socialUser = Socialite::driver($provider)->user();

        $user = User::where('email', '=', $socialUser->email)->first();

        if (!$user) {
            $password = bcrypt(uniqid());

            $request = Request::create('auth/provider', 'GET', [
                'name' => $socialUser->name,
                'email' => $socialUser->email,
                'password' => $password,
                'password_confirmation' => $password
            ]);

            $validator = $this->validator($request->all());

            if ($validator->fails()) {
                $this->throwValidationException(
                    $request, $validator
                );
            }

            $user = $this->create($request->all());
        }

        if (!$user->{$provider . '_id'}) {
            $user->{$provider . '_id'} = $socialUser->id;
            $user->save();
        }

        Auth::guard($this->getGuard())->login($user);

        return redirect($this->redirectPath());
    }
}
