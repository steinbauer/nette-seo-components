<?php

namespace Brabijan\SeoComponents\DI;

use Kdyby\Doctrine\DI\IEntityProvider;
use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\DI\Statement;

class SeoExtension extends CompilerExtension implements IEntityProvider
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$builder->getDefinition('nette.presenterFactory')
				->addSetup('if (method_exists($service, ?)) { $service->setMapping(array(? => ?)); } ' .
					'elseif (property_exists($service, ?)) { $service->mapping[?] = ?; }', array(
						'setMapping', 'Seo', 'Brabijan\SeoComponents\Presenters\*Presenter', 'mapping', 'Seo', 'Brabijan\SeoComponents\Presenters\*Presenter'
					));

		$builder->addDefinition($this->prefix("targetDao"))
				->setClass('Brabijan\SeoComponents\Dao\Target', array(new Statement('@doctrine.dao', array('Brabijan\SeoComponents\Entity\Target'))));

		$builder->addDefinition($this->prefix("routeDao"))
				->setClass('Brabijan\SeoComponents\Dao\Route', array(new Statement('@doctrine.dao', array('Brabijan\SeoComponents\Entity\Route'))));

		$builder->addDefinition($this->prefix("metaDao"))
				->setClass('Brabijan\SeoComponents\Dao\Meta', array(new Statement('@doctrine.dao', array('Brabijan\SeoComponents\Entity\Meta'))));

		$builder->addDefinition($this->prefix("settingsDao"))
				->setClass('Brabijan\SeoComponents\Dao\Settings', array(new Statement('@doctrine.dao', array('Brabijan\SeoComponents\Entity\Settings'))));

		$builder->addDefinition($this->prefix("allowedTargetList"))
				->setClass('Brabijan\SeoComponents\AllowedTargetList');

		$builder->addDefinition($this->prefix("currentTarget"))
				->setClass('Brabijan\SeoComponents\CurrentTarget');

		$builder->addDefinition($this->prefix('router'))
				->setClass('Brabijan\SeoComponents\Router\DbRouter')
				->setAutowired(FALSE)
				->setInject(FALSE);

		$builder->addDefinition($this->prefix("metaTagsControlFactory"))
				->setImplement('Brabijan\SeoComponents\Components\MetaTagsFactory');

		$builder->addDefinition($this->prefix("googleAnalyticsControlFactory"))
				->setImplement('Brabijan\SeoComponents\Components\GoogleAnalyticsFactory');

		$builder->addDefinition($this->prefix("setTargetFactory"))
				->setImplement('Brabijan\SeoComponents\Components\SetTargetFactory');

		$builder->addDefinition($this->prefix("setRouteFactory"))
				->setImplement('Brabijan\SeoComponents\Components\SetRouteFactory');

		$builder->addDefinition($this->prefix("setBaseTitleForm"))
				->setClass('Brabijan\SeoComponents\Forms\SetBaseTitle');

		$builder->addDefinition($this->prefix("setGoogleAnalyticsForm"))
				->setClass('Brabijan\SeoComponents\Forms\SetGoogleAnalytics');

		$builder->addDefinition($this->prefix("setGoogleWebmasterToolsForm"))
				->setClass('Brabijan\SeoComponents\Forms\SetGoogleWebmasterTools');

		$builder->addDefinition($this->prefix("setRobotsFormFactory"))
				->setClass('Brabijan\SeoComponents\Forms\SetRobots');

		$builder->addDefinition($this->prefix("seoContainer"))
				->setParameters(array('Nette\Forms\ControlGroup group' => NULL, 'Brabijan\SeoComponents\Router\Target target' => NULL))
				->setArguments(array($builder::literal('$group'), $builder::literal('$target')))
				->setImplement('Brabijan\SeoComponents\Forms\Controls\SeoContainerFactory')
				->addSetup("injectDependencies");

		$builder->getDefinition('router')
				->addSetup('Brabijan\SeoComponents\Router\DbRouter::prependTo($service, ?)', array($this->prefix('@router')));
	}



	/**
	 * @param Configurator $config
	 * @param string $extensionName
	 */
	public static function register(Configurator $config, $extensionName = 'seoExtension')
	{
		$config->onCompile[] = function (Configurator $config, Compiler $compiler) use ($extensionName) {
			$compiler->addExtension($extensionName, new SeoExtension());
		};
	}



	/**
	 * Returns associative array of Namespace => mapping definition
	 *
	 * @return array
	 */
	public function getEntityMappings()
	{
		return array(
			"Brabijan" => __DIR__ . "/.."
		);
	}

}