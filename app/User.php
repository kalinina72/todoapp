<?php


namespace App;


/**
 * @Entity
 * @Table(name="users")
 */
class User {


	/**
	 * @Id @GeneratedValue @Column(type="integer")
	 * @var int $id
	 **/
	protected $id;
	/**
	 * @Column(type="string", unique=true)
	 * @var string $login
	 **/
	protected $login;
	/**
	 * @Column(type="string")
	 * @var string $title
	 **/
	protected $password_hash;

	/**
	 * @OneToOne(targetEntity="TodoList", mappedBy="owner", cascade={"persist", "remove"})
	 * @var TodoList $todo_list
	 */
	protected $todo_list;

	/**
	 * @param string $password
	 *
	 * @return string
	 */
	public static function hashPassword(string $password): string {
		return password_hash($password, PASSWORD_BCRYPT);
	}

	/**
	 * @param string $password
	 *
	 * @return bool
	 */
	public function verify(string $password): bool {
		return password_verify($password, $this->password_hash);
	}

	/**
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getLogin(): string {
		return $this->login;
	}

	/**
	 * @param string $login
	 */
	public function setLogin(string $login): void {
		$this->login = $login;
	}

	/**
	 * @return string
	 */
	public function getPasswordHash(): string {
		return $this->password_hash;
	}

	/**
	 * @param string $password_hash
	 */
	public function setPasswordHash(string $password_hash): void {
		$this->password_hash = $password_hash;
	}

	/**
	 * @return TodoList
	 */
	public function getTodoList(): TodoList {
		return $this->todo_list;
	}

	/**
	 * @param TodoList $todo_list
	 */
	public function setTodoList(TodoList $todo_list): void {
		$this->todo_list = $todo_list;
		$todo_list->setUser($this);
	}
}