<?php


namespace App;


use Doctrine\ORM\EntityManager;

class Registry {
	/**
	 * @var Registry $instance
	 */
	protected static $data;

	/**
	 * @var EntityManager $entity_manager
	 */
	protected static $entity_manager;

	protected function __construct() {
	}

	public static function init(EntityManager $entity_manager, \Twig_Environment $twig) {
		self::$data['entity_manager'] = $entity_manager;
		self::$data['twig']           = $twig;
	}

	public static function twig(): \Twig_Environment {
		return self::$data['twig'];

	}
//	public static function get(string $key) {
//		return isset(self::$data[$key]) ? self::$data[$key] : null;
//
//	}
//
//	public static function set(string $key, $value) {
//		self::$data[$key] = $value;
//	}

	public static function entityManager(): EntityManager {
		return self::$data['entity_manager'];
	}

}
