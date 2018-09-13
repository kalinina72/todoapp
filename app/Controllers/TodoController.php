<?php


namespace App\Controllers;


use App\Auth;
use App\Registry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TodoController {

	/**
	 * @param Request $request
	 *
	 * @return Response
	 * @throws \Twig_Error_Loader
	 * @throws \Twig_Error_Runtime
	 * @throws \Twig_Error_Syntax
	 */
	public static function indexAction(Request $request): Response {
		if (!Auth::isAuth()) {
			return RedirectResponse::create('/auth');
		}

		return Response::create(
			Registry::twig()->render('app.twig', ['user' => Auth::getAuthUser()])
		);
	}
}