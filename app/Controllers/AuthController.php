<?php


namespace App\Controllers;


use App\Auth;
use App\Exceptions\AuthException;
use App\Registry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController {


	public static function indexAction(Request $request): Response {
		return Response::create(
			Registry::twig()->render('auth.twig', ['user' => Auth::getAuthUser()])
		);
	}

	public static function logoutAction(Request $request): Response {
		Auth::logout();

		return RedirectResponse::create('/auth');
	}


	public static function loginAction(Request $request): Response {
		$login    = filter_var($request->get('login', ''), FILTER_SANITIZE_STRING);
		$password = $request->get('password', '');
		try {
			Auth::login($login, $password);
		}
		catch (AuthException $e) {
			return Response::create(
				Registry::twig()->render('auth.twig', array(
					'error' => $e->getMessage(),
					'user'  => Auth::getAuthUser()
				))
			);
		}

		return RedirectResponse::create('/');
	}

	public static function registerAction(Request $request): Response {
		$login    = filter_var($request->get('login', ''), FILTER_SANITIZE_STRING);
		$password = $request->get('password', '');

		try {
			Auth::register($login, $password);
			Auth::login($login, $password);
		}
		catch (AuthException $e) {
			return Response::create(
				Registry::twig()->render('auth.twig', array(
					'error' => $e->getMessage(),
					'user'  => Auth::getAuthUser()
				))
			);
		}

		return RedirectResponse::create('/');
	}

}