<?php

namespace WebChemistry\Parameters\DI;

use Nette;
use Nette\DI\CompilerExtension;
use WebChemistry\Parameters\ConfigurationException;

class ParametersExtension extends CompilerExtension {

	/** @var array */
	public $defaults = [
		'paramsSettings' => [
			'cache' => TRUE,
			'bar' => '%debugMode%',
			'database' => 'Doctrine',
			'entity' => 'Entity\Parameters'
		]
	];

	/**
	 * Processes configuration data. Intended to be overridden by descendant.
	 *
	 * @throws \Exception
	 */
	public function loadConfiguration() {
		$builder = $this->getContainerBuilder();
		$values = $this->validateConfig($this->defaults);
		$config = Nette\DI\Helpers::expand($values['paramsSettings'], $builder->parameters);
		unset($values['paramsSettings']);

		$databaseClass = strpos($config['database'], '\\') ? $config['database'] : 'WebChemistry\Parameters\Database\\' . $config['database'];
		if (!class_exists($databaseClass)) {
			throw new \Exception("Class '$databaseClass' does not exist.");
		}

		$db = $builder->addDefinition($this->prefix('database'))
			->setClass('WebChemistry\Parameters\IDatabase')
			->setFactory($databaseClass);

		if ($config['database'] === 'Doctrine') {
			$implements = class_implements($config['entity']);
			if (array_search('WebChemistry\Parameters\IEntity', $implements) === FALSE) {
				throw new ConfigurationException("Class '$config[database]' must implements WebChemistry\\Parameters\\IEntity.");
			}
			$db->addSetup('setEntity', [$config['entity']]);
		}

		$builder->addDefinition($this->prefix('provider'))
			->setClass('WebChemistry\Parameters\Provider', [$this->getConfig(), $config['cache'], $this->prefix('@database')]);

		if ($config['bar']) {
			$builder->addDefinition($this->prefix('bar'))
				->setClass('WebChemistry\Parameters\Bar\Debug');
		}
	}

	/**
	 * @param Nette\PhpGenerator\ClassType $class
	 */
	public function afterCompile(Nette\PhpGenerator\ClassType $class) {
		$methods = $class->getMethods();
		$init = $methods['initialize'];

		if ($this->getContainerBuilder()->hasDefinition($this->prefix('bar'))) {
			$init->addBody('if ($this->parameters["debugMode"]) Tracy\Debugger::getBar()->addPanel($this->getService(?));', [$this->prefix('bar')]);
		}
	}

}
