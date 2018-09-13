<?php
declare(strict_types=1);

use App\Auth;
use App\Registry;
use App\TodoList;
use App\User;
use PHPUnit\Framework\TestCase;

final class AuthTest extends TestCase {
	static $login_for_reg = 'logintest123';
	static $login_for_log = 'logintest124';
	static $correct_password = '12345678pwd';
	/**
	 * @var User $user
	 */
	static $user;


	protected static function deleteUserIfExsists($login) {
		$user = Registry::entityManager()->getRepository(User::class)->findOneBy(['login' => $login]);
		if ($user) {
			Registry::entityManager()->remove($user);
			Registry::entityManager()->flush();
		}
	}

	public static function setUpBeforeClass(): void {
		static::deleteUserIfExsists(static::$login_for_log);
		static::deleteUserIfExsists(static::$login_for_reg);
		$user = new User();
		$user->setLogin(static::$login_for_log);
		$user->setPasswordHash(User::hashPassword(static::$correct_password));
		$todo_list = new TodoList();
		$user->setTodoList($todo_list);
		Registry::entityManager()->persist($user);
		Registry::entityManager()->persist($todo_list);
		Registry::entityManager()->flush();
		static::$user = $user;

	}


	public function testCorrectRegistration(): void {
		Auth::register(static::$login_for_reg, static::$correct_password);
		$user = Registry::entityManager()->getRepository(User::class)
		                ->findOneBy(['login' => static::$login_for_reg]);
		$this->assertNotNull($user);
		$this->assertInstanceOf(User::class, $user);
		/**
		 * @var User $user
		 */
		$this->assertEquals(static::$login_for_reg, $user->getLogin());
		$this->assertTrue($user->verify(static::$correct_password));
		$this->assertFalse($user->verify('notPassword'));
		$this->assertNotNull($user->getTodoList());
	}

	public function testErrorForDuplicateRegistrationLogin(): void {
		$this->expectException(\App\Exceptions\AuthException::class);
		Auth::register(static::$user->getLogin(), static::$correct_password);
	}

	public function testCorrectLoginAndLogoutProcessing(): void {
		Auth::login(static::$user->getLogin(), static::$correct_password);
		$this->assertTrue(Auth::isAuth());
		$this->assertEquals(Auth::getAuthUser(), static::$user);
		Auth::logout();
		$this->assertFalse(Auth::isAuth());
	}

	public function testIncorrectLoginPassword(): void {
		$this->expectException(\App\Exceptions\AuthException::class);
		Auth::login(static::$user->getLogin(), 'invalidpassword');
	}

	public function testAuthCheckingForInvalidPassword(): void {
		try {
			Auth::login(static::$user->getLogin(), 'invalidpassword');
		}
		catch (\App\Exceptions\AuthException $e) {
		}
		$this->assertFalse(Auth::isAuth());
		$this->assertNull(Auth::getAuthUser());
		Auth::logout();
		$this->assertFalse(Auth::isAuth());

	}

	public static function tearDownAfterClass(): void {
		static::deleteUserIfExsists(static::$login_for_log);
		static::deleteUserIfExsists(static::$login_for_reg);
	}
}
