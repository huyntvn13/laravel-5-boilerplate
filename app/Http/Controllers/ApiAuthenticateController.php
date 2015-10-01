<?php namespace App\Http\Controllers;

use Dingo\Api\Facade\API;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Input;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Routing\Controller as BaseController;

class ApiAuthenticateController extends BaseController{

  public function authenticate() {

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

  public function validateToken(){
    return API::response()->array(['status' => 'success'])->statusCode(200);
  }

}    