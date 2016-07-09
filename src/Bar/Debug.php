<?php

namespace WebChemistry\Parameters\Bar;

use Nette\Http\Request;
use Nette\Http\Response;
use Tracy\Debugger;
use Tracy\Dumper;
use Tracy\IBarPanel;
use WebChemistry\Parameters\ArrayAccessor;
use WebChemistry\Parameters\Provider;

class Debug implements IBarPanel {

	/** @var \WebChemistry\Parameters\Provider */
	private $provider;

	/** @var \Nette\Http\UrlScript */
	private $url;

	/** @var array */
	private $defaults;

	/** @var array */
	private $parameters;

	/** @var bool */
	private $hasDb;

	/** @var Request */
	private $request;

	/**
	 * @param bool $hasDb
	 * @param Provider $provider
	 * @param Request $request
	 * @param Response $response
	 */
	public function __construct($hasDb, Provider $provider, Request $request, Response $response) {
		$this->provider = $provider;
		$this->url = $request->getUrl();
		$this->hasDb = $hasDb;
		$this->request = $request;

		if ($this->url->getQueryParameter('debug-import-parameters')) {
			$provider->import();
			$this->redirectBack();
		}

		if ($this->url->getQueryParameter('debug-parameters-cache')) {
			$provider->cleanParametersCache();
			$this->redirectBack();
		}
	}

	/**
	 * @return array
	 */
	private function getDefaultParameters() {
		if ($this->defaults === NULL) {
			$this->defaults = $this->provider->getDefaultParameters();
		}

		return $this->defaults;
	}

	/**
	 * @return array
	 */
	private function getParameters() {
		if ($this->parameters === NULL) {
			$this->parameters = $this->provider->getParameters()->getArray();
		}

		return $this->parameters;
	}

	/**
	 * @return string
	 */
	public function getTab() {
		$count = count(array_merge($this->getDefaultParameters(), $this->getParameters()));
		ob_start();
		require __DIR__ . '/templates/tab.phtml';
		return ob_get_clean();
	}

	/**
	 * @return string
	 */
	public function getPanel() {
		$url = $this->url;
		$parameters = $this->getParameters();
		$defaults = $this->getDefaultParameters();
		$hasDb = $this->hasDb;
		ob_start();
		require __DIR__ . '/templates/panel.phtml';
		return ob_get_clean();
	}

	/**
	 * @param mixed $parameter
	 * @return string
	 */
	public static function format($parameter) {
		if ($parameter === NULL) {
			return '<span style="color: blue">NULL</span>';
		} else if ($parameter instanceof ArrayAccessor) {
			ob_start();
			Dumper::dump($parameter->getArray(), [
				Dumper::TRUNCATE => Debugger::$maxLen,
				Dumper::DEPTH => Debugger::$maxDepth,
				Dumper::COLLAPSE => TRUE
			]);
			return ob_get_clean();
		} else if (is_array($parameter)) {
			ob_start();
			Dumper::dump($parameter, [
				Dumper::TRUNCATE => Debugger::$maxLen,
				Dumper::DEPTH => Debugger::$maxDepth,
				Dumper::COLLAPSE => TRUE
			]);
			return ob_get_clean();
		} else {
			return (string) $parameter;
		}
	}

	private function redirectBack() {
		$referrer = $this->request->getReferer();
		$current = $this->request->getUrl();

		if ($referrer && !$referrer->isEqual($current)) {
			header('Location: ' . $referrer);
			exit;
		}
	}

}
