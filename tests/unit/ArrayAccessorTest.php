<?php


class ArrayAccessorTest extends \Codeception\TestCase\Test {

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @return \WebChemistry\Parameters\ArrayAccessor
     */
    private function getArrayAccessor() {
        $array = [
            'first' => 'first',
            'second' => [
                'third' => 'third'
            ],
            'fourth' => [
                'fifth' => 'fifth'
            ]
        ];

        return new \WebChemistry\Parameters\ArrayAccessor($array);
    }

    public function testGettersSetters() {
        $arrayAccess = $this->getArrayAccessor();

        $arrayAccess->second->third = '1';
        $arrayAccess->first = '2';
        $arrayAccess->fourth = ['test' => ['testing']];

        $this->assertSame(['first', 'fourth', 'second'], $arrayAccess->getChanged());
        $this->assertInstanceOf('WebChemistry\Parameters\ArrayAccessor', $arrayAccess->fourth->test);
        $this->assertSame('testing', $arrayAccess->fourth->test[0]);

        unset($arrayAccess->fourth);
        $this->assertNull($arrayAccess->fourth);

        $this->assertSame([
            'first' => '2',
            'second' => [
                'third' => '1'
            ],
            'fourth' => NULL
        ], $arrayAccess->getArray());
    }

    public function testIsset() {
        $arrayAccess = $this->getArrayAccessor();

        $this->assertTrue(isset($arrayAccess['first']));
        $this->assertTrue(isset($arrayAccess['first']));

        $this->assertFalse(isset($arrayAccess['dog']));
        $this->assertFalse(isset($arrayAccess->dog));
    }

    public function testNotExists() {
        $arrayAccess = $this->getArrayAccessor();
        $this->tester->assertExceptionThrown('WebChemistry\Parameters\ParameterNotExistsException', function () use ($arrayAccess) {
            $arrayAccess['notExists']->asd = NULL;
        });
    }

}