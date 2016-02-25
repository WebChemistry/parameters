<?php


class ProviderTest extends \Codeception\TestCase\Test {

    /**
     * @var \UnitTester
     */
    protected $tester;

	/** @var \WebChemistry\Parameters\Provider */
	protected $provider;

	/** @var array */
	protected $defaults = [
		'string' => 'string',
		'empty' => null,
		'number' => 1,
		'float' => 1.5,
		'boolean' => true,
		'array' => [
			'one' => 'one',
			'two' => 'two',
			'three' => [
				'threeOne' => 'threeOne'
			]
		]
	];

    protected function _before() {
		$connection = new \Nette\Database\Connection('mysql:host=localhost;dbname=git', 'root', '');
		$structure = new \Nette\Database\Structure($connection, new \Nette\Caching\Storages\DevNullStorage());
		$context = new \Nette\Database\Context($connection, $structure);
		$database = new \WebChemistry\Parameters\Database\NetteDatabase($context);
		$this->provider = new \WebChemistry\Parameters\Provider($this->defaults, FALSE, $database);
		$this->provider->import();
    }

    protected function _after() {
    }

    public function testDefaultValues() {
		$this->assertSame($this->defaults, $this->provider->getDefaultParameters());
	}

	public function testValuesInDb() {
		// __get
		$this->assertNull($this->provider->empty);
		$this->assertSame('1.5', $this->provider->float);
		$this->assertSame('1', $this->provider->boolean);

		$this->assertInstanceOf('WebChemistry\Parameters\ArrayAccessor', $this->provider->array);
		$this->assertInstanceOf('WebChemistry\Parameters\ArrayAccessor', $this->provider->array->three);

		// Array
		$this->assertNull($this->provider['empty']);
		$this->assertSame('1.5', $this->provider['float']);
	}

	public function testMerge() {
		$this->assertSame([], $this->provider->getParameters()->getChanged());

		$this->provider['float'] = '1.5';
		$this->provider['boolean'] = '3';
		$this->provider->array['one'] = 'two';

		$this->assertSame([
			'float', 'boolean', 'array'
		], $this->provider->getParameters()->getChanged());

		$this->provider->merge();

		$this->assertSame('3', $this->provider->boolean);
		$this->assertSame('two', $this->provider->array->one);

		$this->assertSame('3', $this->provider->boolean);
	}

}
