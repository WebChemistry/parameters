<?php


class ProviderTest extends \Codeception\TestCase\Test {

    /**
     * @var \UnitTester
     */
    protected $tester;

	/** @var \WebChemistry\Parameters\Provider */
	protected $provider;

    protected function _before() {
		$this->provider = E::getByType('WebChemistry\Parameters\Provider');

    }

    protected function _after() {

    }

    public function testDefaultValues() {
		$this->assertSame([
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
		], $this->provider->getDefaultParameters());
	}

	public function testValuesInDb() {
		$this->provider->import();

		// __get
		$this->assertNull($this->provider->empty);
		$this->assertSame('1.5', $this->provider->float);
		$this->assertSame('1', $this->provider->boolean);

		$this->assertInstanceOf('WebChemistry\Parameters\ArrayAccessor', $this->provider->array);

		// Array
		$this->assertNull($this->provider['empty']);
		$this->assertSame('1.5', $this->provider['float']);
	}

	public function testMerge() {
		$this->assertFalse($this->provider->isChanged());

		$this->provider['float'] = '1.5';
		$this->provider['boolean'] = '3';
		$this->provider['array']['one'] = 'two';

		$this->assertTrue($this->provider->isChanged());
		$this->assertSame(['float', 'boolean', 'array'], $this->provider->getParameters()->getChanged());

		$this->provider->merge();

		$this->assertSame('3', $this->provider->boolean);
		$this->assertSame('two', $this->provider->array->one);

		// Clean cache and test new values in database
		$this->provider->cleanParametersCache();

		$this->assertSame('3', $this->provider->boolean);
	}
}