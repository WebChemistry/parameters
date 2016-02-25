<?php

class_alias('EntityManagerMock', 'Doctrine\ORM\EntityManager');

class MockExtension extends \Nette\DI\CompilerExtension {

	public function loadConfiguration() {
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('em'))
			->setClass('EntityManagerMock');
	}

}

class EntityManagerMock {

}