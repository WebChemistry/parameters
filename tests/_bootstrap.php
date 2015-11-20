<?php

class E {

	/** @var \Nette\DI\Container */
	private static $container;

	/** @var array */
	private static $directories = [
		'%data%' => __DIR__ . '/_data',
		'%temp%' => __DIR__ . '/tmp',
		'%www%' => __DIR__ . '/www'
	];

	public function __construct(\Nette\DI\Container $container) {
		self::$container = $container;
	}

	public static function getContainer() {
		return self::$container;
	}

	public static function getByType($class, $need = TRUE) {
		return self::$container->getByType($class, $need);
	}

	public static function initDirectories() {
		foreach (self::$directories as $location) {
			@mkdir($location);
		}
	}

	public static function dir($path) {
		return str_replace(array_keys(self::$directories), array_values(self::$directories), $path);
	}
}

require __DIR__ . '/../../../autoload.php';

$configurator = new Nette\Configurator;

@mkdir(__DIR__ . '/tmp');
$configurator->setTempDirectory(__DIR__ . '/tmp');

$configurator->createRobotLoader()
			 ->addDirectory(__DIR__ . '/../src')
			 ->register();

if (file_exists(E::dir('%data%/config.neon'))) {
	$configurator->addConfig(E::dir('%data%/config.neon'));
}

$container = $configurator->createContainer();

new E($container);
E::initDirectories();
