<?php


namespace App;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


/**
 * @Entity
 * @Table(name="todo_lists")
 */
class TodoList {
	/**
	 * @Id @GeneratedValue @Column(type="integer")
	 * @var int $id
	 **/
	protected $id;


	/**
	 * @OneToOne(targetEntity="User", inversedBy="todo_list", cascade={"persist"})
	 * @JoinColumn(name="owner_id", referencedColumnName="id", onDelete="CASCADE")
	 * @var User $owner
	 */
	protected $owner;

	/**
	 * @OneToMany(targetEntity="Todo", mappedBy="todo_list", cascade={"persist", "remove"})
	 * @var Collection
	 */
	protected $todos;

	public function __construct() {
		$this->todos = new ArrayCollection();
	}

	/**
	 * @return Collection
	 */
	public function getTodos(): Collection {
		return $this->todos;
	}

	/**
	 * @return User
	 */
	public function getUser(): User {
		return $this->owner;
	}

	/**
	 * @param User $owner
	 */
	public function setUser(User $owner): void {
		$this->owner = $owner;
	}

	public function addTodo(Todo $todo): void {
		if (!$this->todos->contains($todo)) {
			$this->todos->add($todo);
			$todo->setTodoList($this);
		}
	}

	/**
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * @param int $id
	 *
	 * @return bool
	 * @throws \Doctrine\ORM\ORMException
	 */
	public function removeTodoById(int $id): bool {
		foreach ($this->todos as $todo) {
			if ($todo->getId() == $id) {
				Registry::entityManager()->remove($todo);

				return true;
			}
		}

		return false;
	}

	/**
	 * @param int $id
	 *
	 * @return Todo|null
	 * @throws \Doctrine\ORM\ORMException
	 */
	public function getTodoById(int $id): ?Todo {
		foreach ($this->todos as $todo) {
			if ($todo->getId() == $id) {
				return $todo;
			}
		}

		return null;
	}

	/**
	 * @return array [["id" => 1, "title" => "title", "is_completed" => true], ...]
	 */
	public function getTodosAsArray(): array {
		if (count($this->todos) == 0) {
			return [];
		}
		$result = [];
		foreach ($this->todos as $todo) {
			$result[] = [
				'id'           => $todo->getId(),
				'title'        => $todo->getTitle(),
				'is_completed' => $todo->isCompleted()
			];
		}

		return $result;
	}
}