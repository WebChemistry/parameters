<?php

namespace WebChemistry\Parameters\DI;

use Nette;
use Nette\DI\CompilerExtension;

class Extension extends CompilerExtension {

	/** @var string */
	public static $databaseClass = 'doctrine';

	/** @var bool */
	public static $useCache = TRUE;

	/** @var bool */
	public static $useDebugBar = TRUE;

	/**
	 * Processes configuration data. Intended to be overridden by descendant.
	 *
	 * @throws \Exception
	 */
	public function loadConfiguration() {
		$builder = $this->getContainerBuilder();

		$databaseClass = 'WebChemistry\Parameters\Database\\' . ucfirst(self::$databaseClass);
		if (!class_exists($databaseClass)) {
			throw new \Exception("Class $databaseClass does not exist.");
		}

		$builder->addDefinition($this->prefix('database'))
			->setClass('WebChemistry\Parameters\IDatabase')
			->setFactory($databaseClass);

		$builder->addDefinition($this->prefix('provider'))
			->setClass('WebChemistry\Parameters\Provider', [$this->getConfig(), self::$useCache, $this->prefix('@database')]);

		$builder->addDefinition($this->prefix('bar'))->setClass('WebChemistry\Parameters\Bar\Debug');
	}

	/**
	 * @param Nette\PhpGenerator\ClassType $class
	 */
	public function afterCompile(Nette\PhpGenerator\ClassType $class) {
		$methods = $class->getMethods();
		$init = $methods['initialize'];

		if (self::$useDebugBar) {
			$init->addBody('if ($this->parameters["debugMode"]) Tracy\Debugger::getBar()->addPanel($this->getService(?));', [$this->prefix('bar')]);
		}
	}

}
