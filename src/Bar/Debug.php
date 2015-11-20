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

	public function __construct(Provider $provider, Request $request, Response $response) {
		$this->provider = $provider;
		$this->url = $request->getUrl();

		if ($this->url->getQueryParameter('debug-import-parameters')) {
			$provider->import();
		}

		if ($this->url->getQueryParameter('debug-parameters-cache')) {
			$provider->cleanParametersCache();
		}
	}

	public function getTab() {
		$count = count($this->provider->getDefaultParameters());
		ob_start();
		require __DIR__ . '/templates/tab.phtml';
		return ob_get_clean();
	}

	public function getPanel() {
		$provider = $this->provider;
		$url = $this->url;
		ob_start();
		require __DIR__ . '/templates/panel.phtml';
		return ob_get_clean();
	}

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

}