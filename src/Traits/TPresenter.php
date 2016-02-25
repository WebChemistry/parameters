<?php

namespace WebChemistry\Parameters\Traits;

use Nette\Application\UI\ITemplate;
use WebChemistry\Parameters\Provider;

trait TPresenter {

	/** @var Provider */
	protected $parametersProvider;

	/**
	 * @param Provider $provider
	 */
	public function injectParametersProvider(Provider $provider) {
		$this->parametersProvider = $provider;
	}

	/**
	 * @return ITemplate
	 */
	protected function createTemplate($template = NULL) {
		/** @var ITemplate $template */
		$template = $template ? : parent::createTemplate();
		$template->parameters = $this->parametersProvider;

		return $template;
	}

}
