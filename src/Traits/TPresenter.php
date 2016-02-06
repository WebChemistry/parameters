<?php

namespace WebChemistry\Parameters\Traits;

use Nette\Application\UI\ITemplate;
use WebChemistry\Parameters\Provider;

trait TPresenter {

	/** @var Provider */
	protected $parametersProvider;

	public function injectParametersProvider(Provider $provider) {
		$this->parametersProvider = $provider;
	}

	/**
	 * @return \Nette\Application\UI\ITemplate
	 */
	protected function createTemplate($template = NULL) {
		/** @var ITemplate $template */
		$template = $template ? : parent::createTemplate();
		$template->parameters = $this->parametersProvider->getParameters();

		return $template;
	}

}
