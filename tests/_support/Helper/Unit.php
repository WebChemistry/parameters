<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Unit extends \Codeception\Module
{

	public function _beforeSuite($settings = []) {
		/** @var \WebChemistry\Parameters\Provider $provider */
		$provider = \E::getByType('WebChemistry\Parameters\Provider');
		$provider->cleanDatabase();
		$provider->cleanParametersCache();
	}

}
