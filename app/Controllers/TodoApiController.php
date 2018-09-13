<?php


namespace App\Controllers;

use App\Auth;
use App\Registry;
use App\Todo;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class TodoApiController {

	/**
	 * @param Request $request (json): [{"id":1},{"id":2}, ...}]
	 *
	 * @return Response success(json) return all todos: {"status":"ok", "result": {"success_count":100, "todos": [{"id":1,"title":"abc","is_completed":true}, ...]}}}
	 *                  fail(json): ["status":"error", "error": "Error msg"]
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	public static function removeAction(Request $request): Response {
		if (!Auth::isAuth()) {
			return JsonResponse::create(['status' => 'error', 'error' => 'not auth']);
		}
		$todos_data = json_decode($request->getContent(), 1);
		if (!$todos_data || count($todos_data) == 0) {
			return JsonResponse::create(['status' => 'error', 'error' => 'can`t find todos in request']);
		}
		$todo_list = Auth::getAuthUser()->getTodoList();

		$success_remove_count = 0;
		foreach ($todos_data as $todo_data) {
			$isRemoved = $todo_list->removeTodoById($todo_data['id'] ?? 0);
			if ($isRemoved) {
				$success_remove_count ++;
			}
		}
		Registry::entityManager()->flush();

		return JsonResponse::create([
			'status' => 'ok',
			'result' => [
				'success_count' => $success_remove_count,
				'todos'         => $todo_list->getTodosAsArray()
			]
		]);
	}

	/**
	 * @param Request $request (json): [{"title":"abc","is_completed":true}, ...]
	 *
	 * @return Response success(json) return all todos:{"status":"ok","result": {"success_count":100, "todos": [{"id":1,"title":"abc","is_completed":true}, ...]}}}
	 *                  fail(json): {"status":"error", "error": "Error msg"}
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	public static function updateAction(Request $request): Response {
		if (!Auth::isAuth()) {
			return JsonResponse::create(['status' => 'error', 'error' => 'not auth']);
		}
		$todos_data = json_decode($request->getContent(), 1);
		if (!$todos_data || count($todos_data) == 0) {
			return JsonResponse::create(['status' => 'error', 'error' => 'can`t find todos in request']);
		}
		$todo_list = Auth::getAuthUser()->getTodoList();


		foreach ($todos_data as $todo_data) {
			if (!$todo = $todo_list->getTodoById(($todo_data['id'] ?? 0))) {
				continue;
			}
			if (isset($todo_data['title'])) {
				$todo->setTitle($todo_data['title']);
			}
			if (isset($todo_data['is_completed'])) {
				$todo->setIsCompleted($todo_data['is_completed']);
			}
			Registry::entityManager()->persist($todo);
		}
		Registry::entityManager()->flush();

		return JsonResponse::create([
			'status' => 'ok',
			'result' => [
				'todos' => $todo_list->getTodosAsArray()
			]
		]);
	}

	/**
	 * @param Request $request (json): [{"title":"abc","is_completed":true}, ...]
	 *
	 * @return Response success(json) return all todos: {"status":"ok", "result": {"success_count":100, "todos": [{"id":1,"title":"abc","is_completed":true}, ...]}}}
	 *                  fail(json): {"status":"error", "error": "Error msg"}
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	public static function addAction(Request $request): Response {
		if (!Auth::isAuth()) {
			return JsonResponse::create(['status' => 'error', 'error' => 'not auth']);
		}
		$todos_data = json_decode($request->getContent(), 1);
		if (!$todos_data || count($todos_data) == 0) {
			return JsonResponse::create(['status' => 'error', 'error' => 'can`t find todos in request']);
		}
		$todo_list = Auth::getAuthUser()->getTodoList();

		foreach ($todos_data as $todo_data) {
			$todo = new Todo();
			$todo->setTitle($todo_data['title'] ?? '');
			$todo->setIsCompleted($todo_data['is_completed'] ?? false);
			$todo_list->addTodo($todo);
			Registry::entityManager()->persist($todo);
		}
		Registry::entityManager()->flush();

		return JsonResponse::create([
			'status' => 'ok',
			'result' => [
				'success_count' => count($todos_data),
				'todos'         => $todo_list->getTodosAsArray()
			]
		]);
	}

	/**
	 * @param Request $request
	 *
	 * @return Response success(json): {"status":"ok", "result": {"todos": [{"id":1,"title":"abc","is_completed":true}, ...]}}}
	 *                  fail(json): {"status":"error", "error": "Error msg"}
	 */
	public static function getAllAction(Request $request): Response {

		if (!Auth::isAuth()) {
			return JsonResponse::create(['status' => 'error', 'error' => 'not auth']);
		}
		$todo_list = Auth::getAuthUser()->getTodoList();

		return JsonResponse::create([
				'status' => 'ok',
				'result' => [
					'todos' => $todo_list->getTodosAsArray()
				]
			]
		);
	}

}