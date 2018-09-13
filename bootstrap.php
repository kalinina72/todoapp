<?php
require_once __DIR__ . "/vendor/autoload.php";

(function () {

	$env   = require_once __DIR__ . "/env.php";
	$paths = array(__DIR__ . "/app");

	$dbParams = array(
		'driver'        => $env['driver'],
		'user'          => $env['username'],
		'password'      => $env['password'],
		'dbname'        => $env['dbname'],
		'charset'       => 'utf8',
	);

	$config         = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration($paths, $env['is_dev_mode']);
	$entity_manager = \Doctrine\ORM\EntityManager::create($dbParams, $config);

	$template_dir = __DIR__ . "/templates";
	$loader       = new Twig_Loader_Filesystem($template_dir);
	$twig         = new Twig_Environment($loader);
	\App\Registry::init($entity_manager, $twig);
})();

session_start();