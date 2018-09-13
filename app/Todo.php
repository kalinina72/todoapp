<?php


namespace App;

/**
 * @Entity
 * @Table(name="todos")
 */
class Todo {

	/**
	 * @Id @Column(type="integer") @GeneratedValue
	 * @var int $id
	 */
	protected $id;

	/**
	 * @Column(type="string")
	 * @var string $title
	 **/
	protected $title;

	/**
	 * @Column(type="boolean")
	 * @var boolean $is_completed
	 **/
	protected $is_completed;

	/**
	 * @ManyToOne(targetEntity="TodoList", inversedBy="todos", cascade={"persist"})
	 * @JoinColumn(onDelete="CASCADE")
	 * @var TodoList $todo_list
	 **/
	protected $todo_list;

	/**
	 * @return TodoList
	 */
	public function getTodoList(): TodoList {
		return $this->todo_list;
	}

	/**
	 * @param TodoList $todo_list
	 */
	public function setTodoList(TodoList $todo_list) {
		$this->todo_list = $todo_list;
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
	public function getTitle(): string {
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function setTitle(string $title): void {
		$this->title = $title;
	}

	/**
	 * @return bool
	 */
	public function isCompleted(): bool {
		return $this->is_completed;
	}

	/**
	 * @param bool $is_completed
	 */
	public function setIsCompleted(bool $is_completed): void {
		$this->is_completed = $is_completed;
	}
}