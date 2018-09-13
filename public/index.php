<?php

require_once __DIR__ . "/../bootstrap.php";

use App\Controllers\AuthController;
use App\Controllers\TodoApiController;
use App\Controllers\TodoController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$routes = [
	"GET"     => [
		"/"              => [TodoController::class, "indexAction"],
		"/auth"          => [AuthController::class, "indexAction"],
		"/auth/logout"   => [AuthController::class, "logoutAction"],
		"/api/todos/all" => [TodoApiController::class, "getAllAction"],
	],
	"POST"    => [
		"/auth/login"       => [AuthController::class, "loginAction"],
		"/auth/register"    => [AuthController::class, "registerAction"],
		"/api/todos/add"    => [TodoApiController::class, "addAction"],
		"/api/todos/remove" => [TodoApiController::class, "removeAction"],
		"/api/todos/update" => [TodoApiController::class, "updateAction"],
	],
	"default" =>
		function (Request $request) {
			return Response::create(
				\App\Registry::twig()->render('404.twig', ['error' => 'Page ' . $request->getPathInfo() . ' not found'])
			);
		}
];

$request     = Request::createFromGlobals();
$router_func = $routes[$request->getMethod()][$request->getPathInfo()] ?? $routes['default'];

$response = call_user_func($router_func, $request);
$response->send();

