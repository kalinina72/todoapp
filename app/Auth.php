<?php


namespace App;


use App\Exceptions\AuthException;
use Doctrine\ORM\ORMException;

class Auth {

	const MIN_LOGIN_LEN = 5;
	const MIN_PASSWORD_LEN = 5;

	protected static $auth_user = null;

	protected function __construct() {
	}

	/**
	 * @param string $login
	 * @param string $password
	 *
	 * @throws AuthException
	 */
	public static function login(string $login, string $password) {
		$user = Registry::entityManager()
		                ->getRepository(User::class)
		                ->findOneBy(['login' => $login]);
		if (!$user) {
			throw new AuthException("Can`t find that login");
		}

		if ($user->verify($password)) {
			self::saveUserToSession($user);
		}
		else {
			throw new AuthException("Invalid password");
		}
	}

	protected static function saveUserToSession(User $user) {
		self::$auth_user  = $user;
		$_SESSION['USER'] = $user->getLogin();
	}

	public static function logout() {
		self::deleteUserFromSession();
	}

	protected static function deleteUserFromSession() {
		$_SESSION['USER'] = null;
		self::$auth_user  = null;
	}

	/**
	 * @param string $login
	 * @param string $password
	 *
	 * @throws AuthException
	 */
	public static function register(string $login, string $password) {
		$user_exists = Registry::entityManager()->getRepository(User::class)->count(["login" => $login]) != 0;
		if ($user_exists) {
			throw new AuthException("That login already exists");
		}
		if (strlen($login) < self::MIN_LOGIN_LEN) {
			throw new AuthException("Login has length < " . self::MIN_LOGIN_LEN);
		}
		if (strlen($password) < self::MIN_PASSWORD_LEN) {
			throw new AuthException("Password has length < " . self::MIN_PASSWORD_LEN);
		}

		$user = new User();
		$user->setLogin($login);
		$user->setPasswordHash(User::hashPassword($password));
		$todo_list = new TodoList();
		$user->setTodoList($todo_list);

		try {
			Registry::entityManager()->persist($user);
			Registry::entityManager()->persist($todo_list);
			Registry::entityManager()->flush();
		}
		catch (ORMException $exception) {
			throw new AuthException("Error during save in db" . $exception->getMessage());
		}
	}

	public static function getAuthUser(): ?User {
		self::loadUserFromSession();

		return self::$auth_user;
	}

	protected static function loadUserFromSession() {
		if (!isset(self::$auth_user) && isset($_SESSION['USER'])) {
			self::$auth_user = Registry::entityManager()
			                           ->getRepository(User::class)
			                           ->findOneBy(['login' => $_SESSION['USER']]);
		}
	}

	public static function isAuth(): bool {
		self::loadUserFromSession();

		return (self::$auth_user !== null);
	}
}