<?php
declare(strict_types=1);

use App\Registry;
use App\Todo;
use App\TodoList;
use App\User;
use PHPUnit\Framework\TestCase;

class TodoTest extends TestCase {
	static $login = 'testtodologin';
	static $password = '12345678pwd';
	/**
	 * @var \App\User $user
	 */
	static $user;


	protected static function deleteUserIfExsists($login) {
		$user = Registry::entityManager()->getRepository(User::class)->findOneBy(['login' => $login]);
		if ($user) {
			Registry::entityManager()->remove($user);
			Registry::entityManager()->flush();
		}
	}

	public static function setUpBeforeClass() {
		static::deleteUserIfExsists(static::$login);
		$user = new User();
		$user->setLogin(static::$login);
		$user->setPasswordHash(User::hashPassword(static::$password));
		$todo_list = new TodoList();
		$user->setTodoList($todo_list);
		Registry::entityManager()->persist($user);
		Registry::entityManager()->persist($todo_list);
		Registry::entityManager()->flush();
		static::$user = $user;
	}

	public static function tearDownAfterClass() {
		static::deleteUserIfExsists(static::$login);
	}

	public function testTodoCreating(): void {
		$todo = new Todo();
		$todo->setTitle('test');
		$todo->setIsCompleted(true);
		static::$user->getTodoList()->addTodo($todo);
		Registry::entityManager()->flush();

		$this->assertTrue($todo->getId() > 0);
		$todos = Registry::entityManager()
		                 ->getRepository(Todo::class)
		                 ->findBy(['todo_list' => static::$user->getTodoList()]);
		$this->assertCount(1, $todos);
		Registry::entityManager()->remove($todos[0]);
		Registry::entityManager()->flush();
	}

	public function testDeletingTodosOnTodoListDeleting(): void {
		$count_of_new_todos = 20;
		$todo_list          = new TodoList();
		Registry::entityManager()->persist($todo_list);
		for ($i = 0; $i < $count_of_new_todos; $i ++) {
			$todo = new Todo();
			$todo->setTitle('test' . $i);
			$todo->setIsCompleted(true);
			$todo_list->addTodo($todo);
		}
		Registry::entityManager()->flush();
		$todo_list_id = $todo_list->getId();
		$this->assertTrue($todo_list_id > 0);
		$todos = Registry::entityManager()
		                 ->getRepository(Todo::class)
		                 ->findBy(['todo_list' => $todo_list]);
		$ids = [];
		foreach ($todos as $todo) {
			$this->assertTrue($todo->getId() > 0);
			$ids[] = $todo->getId();
		}
		$this->assertCount($count_of_new_todos, $todos);
		Registry::entityManager()->remove($todo_list);
		Registry::entityManager()->flush();

		foreach ($ids as $id) {
			$this->assertNull(Registry::entityManager()->find(Todo::class, $id));
		}
		$this->assertNull(Registry::entityManager()->find(TodoList::class, $todo_list_id));

	}

}