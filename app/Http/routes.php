<?php

use App\Http\Controllers\ApiAuthenticateController;

/**
 * Switch between the included languages
 */
require(__DIR__ . "/Routes/Global/Lang.php");

/**
 * Frontend Routes
 * Namespaces indicate folder structure
 */
Route::group(['namespace' => 'Frontend'], function ()
{
	require(__DIR__ . "/Routes/Frontend/Frontend.php");
	require(__DIR__ . "/Routes/Frontend/Access.php");
});

/**
 * Backend Routes
 * Namespaces indicate folder structure
 */
Route::group(['namespace' => 'Backend'], function ()
{
	Route::group(['prefix' => 'admin', 'middleware' => 'auth'], function ()
	{
		/**
		 * These routes need the Administrator Role
		 * or the view-backend permission (good if you want to allow more than one group in the backend, then limit the backend features by different roles or permissions)
		 *
		 * If you wanted to do this in the controller it would be:
		 * $this->middleware('access.routeNeedsRoleOrPermission:{role:Administrator,permission:view_backend,redirect:/,with:flash_danger|You do not have access to do that.}');
		 *
		 * You could also do the above in the Route::group below and remove the other parameters, but I think this is easier to read here.
		 * Note: If you have both, the controller will take precedence.
		 */
		Route::group([
			'middleware' => 'access.routeNeedsRoleOrPermission',
			'role'       => ['Administrator'],
			'permission' => ['view_backend'],
			'redirect'   => '/',
			'with'       => ['flash_danger', 'You do not have access to do that.']
		], function ()
		{
			get('dashboard', ['as' => 'backend.dashboard', 'uses' => 'DashboardController@index']);
			require(__DIR__ . "/Routes/Backend/Access.php");
		});
	});
});

$api = app('Dingo\Api\Routing\Router');
/**
 * API Routes
 */
$api->version('v1', function ($api) {
	$api->resource('auth', 'App\Http\Controllers\ApiAuthenticateController', ['only' => ['index']]);
	$api->post('auth/login', 'App\Http\Controllers\ApiAuthenticateController@login');
	$api->post('auth/signup', 'App\Http\Controllers\ApiAuthenticateController@signup');
	$api->post('auth/recovery', 'App\Http\Controllers\ApiAuthenticateController@recovery');
	$api->post('auth/reset', 'App\Http\Controllers\ApiAuthenticateController@reset');
	$api->post('auth/validate_token', [
    	'protected' => true,
    	'uses'      => 'App\Http\Controllers\ApiAuthenticateController@validateToken',
		'as'        =>  'api.validate_token'
	]);	
	$api->get('auth/user', 'App\Http\Controllers\ApiAuthenticateController@getAuthenticatedUser');

	// example of protected route
	$api->get('protected', ['middleware' => ['jwt.auth'], function () {		
		return \App\User::all();
 	}]);
	// example of free route
	$api->get('free', function() {
		return \App\User::all();
	});
});
