<?php

class ExtensionTest extends \Codeception\TestCase\Test {

	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/** @var \Nette\DI\Compiler */
	protected $compiler;

	protected function _before() {
		$this->compiler = new \Nette\DI\Compiler();

		$this->compiler->addConfig([
			'parameters' => [
				'debugMode' => TRUE
			]
		]);
		$this->compiler->addExtension('params', new \WebChemistry\Parameters\DI\ParametersExtension());
		$this->compiler->addExtension('http', new \Nette\Bridges\HttpDI\HttpExtension());
		$this->compiler->addExtension('mock', new MockExtension());
	}

	protected function _after() {
	}

	public function testCompile() {
		$this->compiler->compile();
	}

}
