<?php

namespace Caffeinated\Themes;

use Caffeinated\Themes\Handlers\ThemesHandler;
use Illuminate\Support\ServiceProvider;

class ThemesServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Boot the service provider.
	 *
	 * @return null
	 */
	public function boot()
	{
		// dd($this->app->databasePath());

		$this->publishes([
			__DIR__.'/../../config/themes.php' => config_path('themes.php')
		]);
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->mergeConfigFrom(
		    __DIR__.'/../../config/themes.php', 'caffeinated.themes'
		);

		$this->registerServices();

		$this->configureTwig();
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['themes', 'themes.components', 'themes.engine'];
	}

	/**
	 * Register the package services.
	 *
	 * @return void
	 */
	protected function registerServices()
	{
		$this->app->bindShared('themes.engine', function ($app) {
			$engine = ucfirst($this->app['config']->get('themes.engine'));

			return $app->make('\Caffeinated\Themes\Engines\\'.$engine.'Engine');
		});

		$this->app->bindShared('themes.components', function($app) {
			return new Components($app, $app['themes.engine']);
		});

		$this->app->bindShared('themes', function($app) {
			return new Themes($app['files'], $app['config'], $app['view']);
		});

		$this->app->booting(function($app) {
			$app['themes']->register();
		});
	}

	/**
	 * Configure Twig
	 *
	 * Registers the necessary Caffeinated Themes extensions and facades
	 * with Twig; only if Twig is set as the template engine.
	 *
	 * @return null
	 */
	protected function configureTwig()
	{
		$engine = $this->app['config']->get('themes.engine');
		
		if ($engine == 'twig') {
			$this->app['config']->push(
				'twigbridge.extensions.enabled',
				'Caffeinated\Themes\Twig\Extensions\Component'
			);
		}
	}
}
