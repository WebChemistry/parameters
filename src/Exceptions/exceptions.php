<?php

namespace WebChemistry\Parameters;

use Exception;

class ParameterNotExistsException extends \Exception {

	public function __construct($name, $code = 0, Exception $previous = NULL) {
		parent::__construct('Parameter "' . $name . '" not exists.', $code, $previous);
	}

}

class ConfigurationException extends \Exception {}
