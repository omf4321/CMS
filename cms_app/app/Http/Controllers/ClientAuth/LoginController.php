<?php

namespace App\Http\Controllers\ClientAuth;

use App\Client;
use App\Model\Admin\student;
use App\Http\Controllers\Controller;
use Hesto\MultiAuth\Traits\LogsoutGuard;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use JWTAuth;
use Validator;
use Carbon\Carbon;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers, LogsoutGuard {
        LogsoutGuard::logout insteadof AuthenticatesUsers;
    }

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    public $redirectTo = '/client/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('client.guest', ['except' => 'logout']);
        // $this->middleware('auth:api', ['except' => ['api_login']]);
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('client.auth.login');
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('client');
    }

    public function username()
    {
        return 'username';
    }

    protected function attemptLogin(Request $request)
    {
        $user = student::where('reg_no', $request->username)->where('status', 'present')->first();
        if ($user) {
            return $this->guard()->attempt(
                $this->credentials($request), $request->filled('remember')
            );
        } else {
            throw ValidationException::withMessages([
                'reg_no' => ['Registration number not exist or inactive']
            ]);
        }
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        $user = client::where('username', $request->username)->first();
        if (!$user) {
            throw ValidationException::withMessages([
                'reg_no' => ['Registration number not exist']
            ]);
        }
        throw ValidationException::withMessages([
            'password' => ['The password did\'t match']
        ]);
    }

    public function api_login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reg_no' => 'required|max:255',
            'password' => 'required|min:8|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Please enter valid reg no and password'
            ]);
        }

        $remember = 1;
        $request->username = $request->reg_no;

        $user = student::where('reg_no', $request->username)->where('status', 'present')->select('id', 'reg_no', 'batch_id', 'name', 'client_id', 'mobile2', 'father_name', 'mother_name', 'address', 'date_of_admit', 'date_of_birth', 'photo', 'updated_at')->first();
        if ($user) {
            $login = Auth::attempt(['username' => $request->username, 'password' => $request->password], $remember);
            if ($login) {
                $user->batch_name = $user->batches->name;
                $user->email = Auth::user()->email ? Auth::user()->email : "";
                $user->dob = Carbon::parse($user->date_of_birth)->format('d M y');
                $user->profile_pic_url = env('APP_URL') . '/storage/avatar/' . $user->batch_name . '/' . $user->photo;
                return response()->json(['message' => 'Login Succesfully', 'user' => $user, 'error' => false]);
            } else {
                return response()->json(['error' => true, 'message' => 'Wrong password']);
            }
        } else {
            return response()->json(['message' => 'Registration number not exist or inactive', 'error' => true]);
        }


        // =========== on jwt token login ====================
        // ===================================================
        // if ($token = JWTAuth::attempt($credentials)) {
        //     return response()->json([
        //         'error' => false,
        //         'token' => $token,
        //         'user' => Auth::guard('api')->user(),
        //         'message' => 'Ok'
        //     ]);
        // }

        // return response()->json([
        //     'error' => true,
        //     'message' => 'Email or Password did not match'
        // ]);
    }


}
