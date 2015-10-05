<?php namespace App\Http\Controllers;

use App\User;
use Dingo\Api\Facade\API;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Input;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Routing\Controller as BaseController;
use App\Http\Requests\Frontend\Access\RegisterRequest;
use App\Repositories\Frontend\Auth\AuthenticationContract;

class ApiAuthenticateController extends BaseController{

  public function __construct(AuthenticationContract $auth)
  {
    // Apply the jwt.auth middleware to all methods in this controller
    // except for the authenticate method. We don't want to prevent
    // the user from retrieving their token if they don't already have it
    $this->middleware('jwt.auth', ['except' => ['login', 'signup']]);

    $this->auth = $auth;
  }

  public function index() {
    // Retrieve all the users in the database and return them
    $users = User::all();
    return $users;
  }   

  public function login() {

    // Get credentials from the request
    $credentials = Input::only('email', 'password');

    try {
      // Attempt to verify the credentials and create a token for the user.
      if (! $token = JWTAuth::attempt($credentials)) {
        return API::response()->array(['error' => 'invalid_credentials'])->statusCode(401);
      }
    } catch (JWTException $e) {
      // Something went wrong - let the app know.
      return API::response()->array(['error' => 'could_not_create_token'])->statusCode(500);
    }

    // Return success.
    return compact('token');
  }

  public function signup()
  {
    return $this->auth->create(Input::all());
  }

  public function validateToken(){
    return API::response()->array(['status' => 'success'])->statusCode(200);
  }

  public function getAuthenticatedUser() {
    try {

      if (! $user = JWTAuth::parseToken()->authenticate()) {
          return response()->json(['user_not_found'], 404);
      }

    } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

      return response()->json(['token_expired'], $e->getStatusCode());

    } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

      return response()->json(['token_invalid'], $e->getStatusCode());

    } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

      return response()->json(['token_absent'], $e->getStatusCode());

    }

    // the token is valid and we have found the user via the sub claim
    return response()->json(compact('user'));
  }

}    